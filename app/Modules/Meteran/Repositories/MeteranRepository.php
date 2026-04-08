<?php

namespace App\Modules\Meteran\Repositories;

use App\Modules\Meteran\Models\MeteranReading;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Support\Collection;

class MeteranRepository extends BaseRepository
{
    protected string $model = MeteranReading::class;

    // ── Query Helpers ────────────────────────────────────────────────

    /**
     * Apakah sudah ada pembacaan untuk pelanggan + periode ini?
     * Dipakai oleh MeteranService sebelum insert.
     */
    public function existsForPeriode(int $pelangganId, string $periode): bool
    {
        return $this->query()
            ->where('pelanggan_id', $pelangganId)
            ->where('periode', $periode)
            ->exists();
    }

    public function findByPelangganDanPeriode(int $pelangganId, string $periode): ?MeteranReading
    {
        return $this->query()
            ->where('pelanggan_id', $pelangganId)
            ->where('periode', $periode)
            ->first();
    }

    /**
     * Ambil kubik_akhir dari bulan sebelumnya — untuk pre-fill kubik_awal bulan ini.
     */
    public function getKubikAkhirBulanLalu(int $pelangganId, string $periode): ?float
    {
        [$tahun, $bulan] = explode('-', $periode);
        $bulanLalu = \Carbon\Carbon::create($tahun, $bulan, 1)
            ->subMonth()
            ->format('Y-m');

        $reading = $this->query()
            ->where('pelanggan_id', $pelangganId)
            ->where('periode', $bulanLalu)
            ->value('kubik_akhir');

        return $reading !== null ? (float) $reading : null;
    }

    /**
     * Data pemakaian N bulan terakhir untuk grafik.
     */
    public function getPemakaian6BulanTerakhir(int $pelangganId, int $nBulan = 6): Collection
    {
        return $this->query()
            ->where('pelanggan_id', $pelangganId)
            ->where('periode', '>=', now()->subMonths($nBulan)->format('Y-m'))
            ->select(['periode', 'pemakaian', 'kubik_awal', 'kubik_akhir'])
            ->orderBy('periode')
            ->get();
    }

    /**
     * Daftar pelanggan aktif yang belum di-input meterannya bulan ini.
     */
    public function pelangganBelumInputBulanIni(): Collection
    {
        $periode = now()->format('Y-m');
        return \App\Modules\Pelanggan\Models\Pelanggan::aktif()
            ->whereDoesntHave('meterReadings', fn($q) => $q->where('periode', $periode))
            ->get(['id', 'nomor_pelanggan', 'nama']);
    }

    public function countBelumInputBulanIni(): int
    {
        return $this->pelangganBelumInputBulanIni()->count();
    }

    public function totalPemakaianPerTahun(int $pelangganId, int $tahun): float
    {
        return (float) $this->query()
            ->where('pelanggan_id', $pelangganId)
            ->where('periode', 'like', $tahun . '-%')
            ->sum('pemakaian');
    }
}
