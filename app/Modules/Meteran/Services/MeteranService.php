<?php

namespace App\Modules\Meteran\Services;

use App\Modules\Meteran\Events\MeteranDibaca;
use App\Modules\Meteran\Exceptions\KubikTidakValidException;
use App\Modules\Meteran\Exceptions\MeteranSudahDiinputException;
use App\Modules\Meteran\Models\MeteranReading;
use App\Modules\Meteran\Repositories\MeteranRepository;
use App\Modules\Pelanggan\Models\Pelanggan;
use App\Modules\Pelanggan\Repositories\PelangganRepository;
use App\Modules\Pelanggan\Services\PelangganService;
use App\Modules\Shared\Contracts\EventPublisherInterface;
use App\Modules\Shared\Models\EventLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MeteranService
{
    public function __construct(
        private readonly MeteranRepository    $meteranRepo,
        private readonly PelangganRepository  $pelangganRepo,
        private readonly PelangganService     $pelangganService,
        private readonly EventPublisherInterface $publisher,
    ) {}


    // ── Input Meteran ────────────────────────────────────────────────

    /**
     * Catat pembacaan meteran baru.
     *
     * Flow:
     * 1. Validasi pelanggan aktif
     * 2. Validasi periode belum ada (unique per pelanggan + periode)
     * 3. Validasi kubik_akhir >= kubik_awal
     * 4. Hitung pemakaian
     * 5. Simpan dalam DB::transaction
     * 6. Dispatch MeteranDibaca event (async → TriggerGenerateTagihan)
     *
     * @param  array{
     *     pelanggan_id: int,
     *     periode: string,
     *     kubik_awal: float,
     *     kubik_akhir: float,
     *     dicatat_oleh?: int,
     *     foto_meteran?: string,
     *     catatan?: string
     * } $data
     */
    public function catat(array $data, int $dicatatOleh): MeteranReading
    {
        $pelanggan = $this->pelangganRepo->findOrFail($data['pelanggan_id']);

        // ── Guard 1: pelanggan harus aktif ───────────────────────────
        $this->pelangganService->assertAktif($pelanggan);

        // ── Guard 2: periode belum ada untuk pelanggan ini ───────────
        $this->assertPeriodeBelumAda($pelanggan->id, $data['periode']);

        // ── Guard 3: kubik akhir >= kubik awal ───────────────────────
        $kubikAwal  = (float) $data['kubik_awal'];
        $kubikAkhir = (float) $data['kubik_akhir'];
        $this->assertKubikValid($kubikAwal, $kubikAkhir);

        $pemakaian = round($kubikAkhir - $kubikAwal, 2);

        // ── Simpan dalam transaction ──────────────────────────────────
        $reading = DB::transaction(function () use (
            $data, $pelanggan, $kubikAwal, $kubikAkhir, $pemakaian, $dicatatOleh
        ) {
            return $this->meteranRepo->create([
                'pelanggan_id'  => $pelanggan->id,
                'periode'       => $data['periode'],
                'kubik_awal'    => $kubikAwal,
                'kubik_akhir'   => $kubikAkhir,
                'pemakaian'     => $pemakaian,
                'dicatat_oleh'  => $dicatatOleh,
                'foto_meteran'  => $data['foto_meteran'] ?? null,
                'catatan'       => $data['catatan'] ?? null,
            ]);
        });

        // ── Dispatch ke Laravel Event System (GenerateTagihanListener) ──
        $event = new MeteranDibaca($reading);
        event($event);

        // ── Publish ke RabbitMQ (AMQP) ───────────────────────────────
        $this->publisher->publish(
            exchange:   config('rabbitmq.exchanges.events', 'sab.events'),
            routingKey: config('rabbitmq.routing_keys.MeteranDibaca', 'meteran.dibaca'),
            payload:    $event->toPayload(),
        );

        // ── Catat di event_logs ───────────────────────────────────────
        EventLog::catat(
            eventName:     $event->eventName(),
            aggregateType: $event->aggregateType(),
            aggregateId:   $event->aggregateId(),
            payload:       $event->toPayload(),
        );

        return $reading;
    }

    // ── Update Meteran ───────────────────────────────────────────────

    /**
     * Koreksi pembacaan meteran yang sudah ada.
     * Hanya bisa dilakukan jika tagihan belum diterbitkan (masih draft).
     */
    public function koreksi(int $readingId, array $data): MeteranReading
    {
        return DB::transaction(function () use ($readingId, $data) {
            /** @var MeteranReading $reading */
            $reading = $this->meteranRepo->findOrFail($readingId);

            // Guard: tidak bisa koreksi jika tagihan sudah terbit
            if ($reading->tagihan()->whereNotIn('status', ['draft'])->exists()) {
                throw new \RuntimeException(
                    'Tidak dapat mengoreksi meter reading karena tagihan sudah diterbitkan.',
                    422
                );
            }

            $kubikAwal  = (float) ($data['kubik_awal']  ?? $reading->kubik_awal);
            $kubikAkhir = (float) ($data['kubik_akhir'] ?? $reading->kubik_akhir);

            $this->assertKubikValid($kubikAwal, $kubikAkhir);

            return $this->meteranRepo->update($readingId, [
                'kubik_awal'   => $kubikAwal,
                'kubik_akhir'  => $kubikAkhir,
                'pemakaian'    => round($kubikAkhir - $kubikAwal, 2),
                'catatan'      => $data['catatan'] ?? $reading->catatan,
                'foto_meteran' => $data['foto_meteran'] ?? $reading->foto_meteran,
            ]);
        });
    }

    // ── Read ─────────────────────────────────────────────────────────

    /**
     * Ambil kubik_akhir bulan lalu untuk pre-fill form input meteran.
     */
    public function getKubikAwalSuggestion(int $pelangganId, string $periode): ?float
    {
        return $this->meteranRepo->getKubikAkhirBulanLalu($pelangganId, $periode);
    }

    public function getPemakaianGrafik(int $pelangganId, int $nBulan = 6): Collection
    {
        return $this->meteranRepo
            ->getPemakaian6BulanTerakhir($pelangganId, $nBulan)
            ->map(fn($r) => [
                'label'     => \Carbon\Carbon::createFromFormat('Y-m', $r->periode)
                                   ->translatedFormat('M Y'),
                'pemakaian' => (float) $r->pemakaian,
                'abnormal'  => (float) $r->pemakaian > config('sab.max_kubik_normal', 20),
            ]);
    }

    public function getBelumInput(): Collection
    {
        return $this->meteranRepo->pelangganBelumInputBulanIni();
    }

    // ── Private Guards ───────────────────────────────────────────────

    private function assertPeriodeBelumAda(int $pelangganId, string $periode): void
    {
        if ($this->meteranRepo->existsForPeriode($pelangganId, $periode)) {
            throw new MeteranSudahDiinputException($periode);
        }
    }

    private function assertKubikValid(float $awal, float $akhir): void
    {
        if ($akhir < $awal) {
            throw new KubikTidakValidException($awal, $akhir);
        }
    }
}
