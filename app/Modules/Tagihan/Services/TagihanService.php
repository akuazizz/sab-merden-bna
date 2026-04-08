<?php

namespace App\Modules\Tagihan\Services;

use App\Modules\Meteran\Models\MeteranReading;
use App\Modules\Shared\Contracts\EventPublisherInterface;
use App\Modules\Shared\Models\EventLog;
use App\Modules\Shared\Repositories\PengaturanRepository;
use App\Modules\Tagihan\Enums\TagihanStatus;
use App\Modules\Tagihan\Events\TagihanDibuat;
use App\Modules\Tagihan\Events\TagihanDivoid;
use App\Modules\Tagihan\Exceptions\TagihanSudahAdaException;
use App\Modules\Tagihan\Exceptions\TransisiStatusTidakValidException;
use App\Modules\Tagihan\Models\Tagihan;
use App\Modules\Tagihan\Repositories\TagihanRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagihanService
{
    public function __construct(
        private readonly TagihanRepository       $tagihanRepo,
        private readonly PengaturanRepository    $pengaturan,
        private readonly EventPublisherInterface $publisher,
    ) {}


    // ── Generate Tagihan ─────────────────────────────────────────────

    /**
     * Generate tagihan dari sebuah meter reading.
     * Dipanggil oleh: TriggerGenerateTagihan listener (async)
     *                  atau manual oleh admin.
     *
     * Flow:
     * 1. Guard: cek tagihan belum ada untuk reading ini
     * 2. Ambil snapshot harga dari pengaturan (bukan harga saat bayar)
     * 3. Hitung total = (pemakaian × harga) + biaya_admin
     * 4. Generate nomor_tagihan unik
     * 5. Simpan dalam DB::transaction
     * 6. Dispatch TagihanDibuat event
     */
    public function generate(MeteranReading $reading): Tagihan
    {
        // ── Guard: idempotency ────────────────────────────────────────
        if ($this->tagihanRepo->existsForReading($reading->id)) {
            throw new TagihanSudahAdaException($reading->id);
        }

        // ── Ambil snapshot config harga ───────────────────────────────
        $hargaPerKubik = $this->pengaturan->getFloat('harga_per_kubik', 2500);
        $biayaAdmin    = $this->pengaturan->getFloat('biaya_admin', 5000);
        $batasTgl      = $this->pengaturan->getInt('batas_jatuh_tempo', 20);

        // ── Kalkulasi ─────────────────────────────────────────────────
        $subtotal      = (float) $reading->pemakaian * $hargaPerKubik;
        $totalTagihan  = $subtotal + $biayaAdmin;
        $jatuhTempo    = $this->hitungJatuhTempo($reading->periode, $batasTgl);
        $nomorTagihan  = $this->generateNomorTagihan($reading->periode);

        // ── Simpan dalam transaction ──────────────────────────────────
        $tagihan = DB::transaction(function () use (
            $reading, $nomorTagihan, $hargaPerKubik, $biayaAdmin,
            $totalTagihan, $jatuhTempo
        ) {
            return $this->tagihanRepo->create([
                'pelanggan_id'        => $reading->pelanggan_id,
                'meter_reading_id'    => $reading->id,
                'nomor_tagihan'       => $nomorTagihan,
                'periode'             => $reading->periode,
                'pemakaian_kubik'     => $reading->pemakaian,
                'harga_per_kubik'     => $hargaPerKubik,
                'biaya_admin'         => $biayaAdmin,
                'denda'               => 0,
                'total_tagihan'       => $totalTagihan,
                'status'              => TagihanStatus::Terbit->value,
                'tanggal_terbit'      => now()->toDateString(),
                'tanggal_jatuh_tempo' => $jatuhTempo,
            ]);
        });

        // ── Dispatch event ke Laravel Event System + RabbitMQ ──────────
        $dibuat = new TagihanDibuat($tagihan);
        event($dibuat);
        $this->publisher->publish(
            exchange:   config('rabbitmq.exchanges.events', 'sab.events'),
            routingKey: config('rabbitmq.routing_keys.TagihanDibuat', 'tagihan.dibuat'),
            payload:    $dibuat->toPayload(),
        );
        EventLog::catat(
            eventName:     $dibuat->eventName(),
            aggregateType: $dibuat->aggregateType(),
            aggregateId:   $dibuat->aggregateId(),
            payload:       $dibuat->toPayload(),
        );

        return $tagihan;
    }

    // ── Transisi Status (State Machine) ──────────────────────────────

    /**
     * Ubah status tagihan mengikuti state machine yang ketat.
     *
     * State machine diterapkan di DUA level:
     * 1. TagihanStatus enum (allowedTransitions) — validasi domain logic
     * 2. Method ini — orchestrasi side-effects (dispatch event, set tanggal)
     */
    public function transisiStatus(
        Tagihan       $tagihan,
        TagihanStatus $statusBaru,
        ?string       $catatan  = null,
        ?string       $alasan   = null,
    ): Tagihan {
        // ── Validasi state machine ────────────────────────────────────
        if (!$tagihan->status->canTransitionTo($statusBaru)) {
            throw new TransisiStatusTidakValidException($tagihan->status, $statusBaru);
        }

        return DB::transaction(function () use ($tagihan, $statusBaru, $catatan, $alasan) {
            $update = ['status' => $statusBaru->value];

            // Side-effect: set tanggal_lunas saat lunas
            if ($statusBaru === TagihanStatus::Lunas) {
                $update['tanggal_lunas'] = now();
            }

            // Side-effect: tambah denda saat mark as jatuh_tempo
            if ($statusBaru === TagihanStatus::JatuhTempo) {
                $denda = $this->hitungDenda($tagihan);
                if ($denda > 0) {
                    $update['denda']          = $denda;
                    $update['total_tagihan']  = (float) $tagihan->total_tagihan + $denda;
                }
            }

            if ($catatan) {
                $update['catatan'] = $catatan;
            }

            $tagihanUpdated = $this->tagihanRepo->update($tagihan->id, $update);

            // ── Dispatch event untuk void + RabbitMQ ────────────────
            if ($statusBaru === TagihanStatus::Void) {
                $voidEv = new TagihanDivoid($tagihanUpdated, $alasan);
                event($voidEv);
                $this->publisher->publish(
                    exchange:   config('rabbitmq.exchanges.events', 'sab.events'),
                    routingKey: config('rabbitmq.routing_keys.TagihanDivoid', 'tagihan.divoid'),
                    payload:    $voidEv->toPayload(),
                );
                EventLog::catat(
                    eventName:     $voidEv->eventName(),
                    aggregateType: $voidEv->aggregateType(),
                    aggregateId:   $voidEv->aggregateId(),
                    payload:       $voidEv->toPayload(),
                );
            }

            // ── Catat di event_logs ───────────────────────────────────
            EventLog::catat(
                eventName:     "TagihanStatus:{$tagihan->status->value}->{$statusBaru->value}",
                aggregateType: 'Tagihan',
                aggregateId:   $tagihan->id,
                payload: [
                    'nomor_tagihan' => $tagihan->nomor_tagihan,
                    'status_lama'   => $tagihan->status->value,
                    'status_baru'   => $statusBaru->value,
                    'alasan'        => $alasan,
                ],
            );

            return $tagihanUpdated;
        });
    }

    /**
     * Shortcut: void tagihan oleh admin.
     */
    public function void(Tagihan $tagihan, string $alasan): Tagihan
    {
        return $this->transisiStatus(
            $tagihan,
            TagihanStatus::Void,
            catatan: "Dibatalkan: {$alasan}",
            alasan:  $alasan,
        );
    }

    /**
     * Shortcut: tandai lunas (dipakai oleh PaymentService setelah sukses).
     */
    public function tandaiLunas(Tagihan $tagihan): Tagihan
    {
        return $this->transisiStatus($tagihan, TagihanStatus::Lunas);
    }

    // ── Scheduler Actions ─────────────────────────────────────────────

    /**
     * Tandai semua tagihan yang sudah lewat jatuh tempo.
     * Dipanggil oleh scheduler harian jam 00:01.
     *
     * Menggunakan bulk update langsung ke DB (tidak via loop model)
     * agar efisien untuk hunderts of records.
     *
     * @return int Jumlah tagihan yang diupdate
     */
    public function markOverdue(): int
    {
        $updated = $this->tagihanRepo->markOverdue();

        // Log untuk observability
        if ($updated > 0) {
            EventLog::catat(
                eventName:     'TagihanBulkMarkOverdue',
                aggregateType: 'Tagihan',
                aggregateId:   0,
                payload: [
                    'jumlah_diupdate' => $updated,
                    'tanggal'         => now()->toDateString(),
                ],
            );
        }

        return $updated;
    }

    // ── Read ──────────────────────────────────────────────────────────

    public function getByPelanggan(int $pelangganId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->tagihanRepo->getByPelanggan($pelangganId, $perPage);
    }

    public function getDetailOrFail(string $nomorTagihan, int $pelangganId): Tagihan
    {
        $tagihan = $this->tagihanRepo->findByNomor($nomorTagihan);

        if (!$tagihan || $tagihan->pelanggan_id !== $pelangganId) {
            abort(404); // ownership check — jangan bocorkan eksistensi
        }

        return $tagihan->load(['meterReading', 'transaksi' => fn($q) => $q->latest()->limit(5)]);
    }

    public function getOutstanding(?string $periode = null): Collection
    {
        return $this->tagihanRepo->getOutstanding($periode);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Hitung denda terlambat berdasarkan pengaturan.
     * Denda = total_tagihan × persen_denda / 100
     */
    public function hitungDenda(Tagihan $tagihan): float
    {
        $dendaPersen = $this->pengaturan->getFloat('denda_persen', 5);

        if ($dendaPersen <= 0) {
            return 0;
        }

        return round((float) $tagihan->total_tagihan * ($dendaPersen / 100), 0);
    }

    /**
     * Generate nomor tagihan unik: INV-202401-001
     * Race condition sangat kecil (1 periode = 1 bulan, volume rendah).
     */
    private function generateNomorTagihan(string $periode): string
    {
        $periodeSlug = str_replace('-', '', $periode); // '202401'
        $urutan      = $this->tagihanRepo->countByPeriodeForNomor($periode) + 1;

        return sprintf('INV-%s-%03d', $periodeSlug, $urutan);
    }

    /**
     * Hitung tanggal jatuh tempo: tanggal $batasTgl di bulan $periode.
     * Jika periode = '2024-01' dan batasTgl = 20 → '2024-01-20'
     */
    private function hitungJatuhTempo(string $periode, int $batasTgl): string
    {
        [$tahun, $bulan] = explode('-', $periode);

        // Clamp ke akhir bulan (misal Feb tidak punya tanggal 30)
        $maxHari = Carbon::create((int) $tahun, (int) $bulan)->daysInMonth;
        $hari    = min($batasTgl, $maxHari);

        return Carbon::create((int) $tahun, (int) $bulan, $hari)->toDateString();
    }
}
