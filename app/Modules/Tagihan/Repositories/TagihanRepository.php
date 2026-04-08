<?php

namespace App\Modules\Tagihan\Repositories;

use App\Modules\Tagihan\Models\Tagihan;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TagihanRepository extends BaseRepository
{
    protected string $model = Tagihan::class;

    // ── Query Helpers ────────────────────────────────────────────────

    public function findByNomor(string $nomorTagihan): ?Tagihan
    {
        return $this->query()->where('nomor_tagihan', $nomorTagihan)->first();
    }

    /**
     * Guard: apakah tagihan sudah ada untuk meter_reading_id ini?
     * Mencegah duplikasi generate tagihan.
     */
    public function existsForReading(int $meterReadingId): bool
    {
        return $this->query()
            ->where('meter_reading_id', $meterReadingId)
            ->exists();
    }

    public function getByPelanggan(int $pelangganId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()
            ->where('pelanggan_id', $pelangganId)
            ->whereNotIn('status', ['draft'])
            ->with(['meterReading:id,pemakaian,periode'])
            ->orderByDesc('periode')
            ->paginate($perPage);
    }

    public function getOutstanding(?string $periode = null): Collection
    {
        return $this->query()
            ->outstanding()
            ->when($periode, fn($q) => $q->where('periode', $periode))
            ->with(['pelanggan:id,nomor_pelanggan,nama'])
            ->orderBy('tanggal_jatuh_tempo')
            ->get();
    }

    public function totalOutstanding(): float
    {
        return (float) $this->query()->outstanding()->sum('total_tagihan');
    }

    public function totalOutstandingByPelanggan(int $pelangganId): float
    {
        return (float) $this->query()
            ->outstanding()
            ->where('pelanggan_id', $pelangganId)
            ->sum('total_tagihan');
    }

    public function countByPeriode(string $periode): int
    {
        return $this->query()
            ->where('periode', $periode)
            ->whereNotIn('status', ['draft', 'void'])
            ->count();
    }

    public function pendapatanBulanIni(): float
    {
        return (float) $this->query()
            ->lunas()
            ->where('periode', now()->format('Y-m'))
            ->sum('total_tagihan');
    }

    /**
     * Tandai tagihan yang sudah melewati jatuh tempo sebagai 'jatuh_tempo'.
     * Dijalankan oleh scheduler harian.
     *
     * @return int Jumlah tagihan yang diupdate
     */
    public function markOverdue(): int
    {
        return $this->query()
            ->whereIn('status', ['terbit', 'sebagian'])
            ->where('tanggal_jatuh_tempo', '<', now()->toDateString())
            ->update(['status' => 'jatuh_tempo']);
    }

    /**
     * Nomor urut dalam satu periode untuk generate nomor_tagihan.
     */
    public function countByPeriodeForNomor(string $periode): int
    {
        return $this->query()->where('periode', $periode)->count();
    }

    public function getByPelangganDanTahun(int $pelangganId, int $tahun): Collection
    {
        return $this->query()
            ->where('pelanggan_id', $pelangganId)
            ->tahun($tahun)
            ->orderBy('periode')
            ->get();
    }

    public function totalLunasPerPelanggan(int $pelangganId, int $tahun): float
    {
        return (float) $this->query()
            ->where('pelanggan_id', $pelangganId)
            ->lunas()
            ->tahun($tahun)
            ->sum('total_tagihan');
    }
}
