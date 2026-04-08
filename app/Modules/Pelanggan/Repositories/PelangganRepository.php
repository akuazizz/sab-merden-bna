<?php

namespace App\Modules\Pelanggan\Repositories;

use App\Modules\Pelanggan\Models\Pelanggan;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PelangganRepository extends BaseRepository
{
    protected string $model = Pelanggan::class;

    // ── Query Helpers ────────────────────────────────────────────────

    public function findByNomor(string $nomor): ?Pelanggan
    {
        return $this->query()->where('nomor_pelanggan', $nomor)->first();
    }

    public function findByUserId(int $userId): ?Pelanggan
    {
        return $this->query()->where('user_id', $userId)->first();
    }

    /**
     * Hitung pelanggan aktif di tahun tertentu (untuk generate nomor urut).
     */
    public function countByTahun(int $tahun): int
    {
        return $this->query()
            ->whereYear('tanggal_daftar', $tahun)
            ->count();
    }

    public function countByStatus(string $status): int
    {
        return $this->query()->where('status', $status)->count();
    }

    /**
     * Apakah pelanggan ini masih punya tagihan yang belum selesai?
     * Dipakai sebelum deactivate/delete untuk guard bisnis.
     */
    public function hasTagihanAktif(int $pelangganId): bool
    {
        return $this->query()
            ->where('id', $pelangganId)
            ->whereHas('tagihanAktif')
            ->exists();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->withCount('tagihanAktif')   // untuk badge di tabel admin
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('nomor_pelanggan', 'like', "%{$keyword}%")
                  ->orWhere('nik', 'like', "%{$keyword}%")
                  ->orWhere('telepon', 'like', "%{$keyword}%");
            })
            ->paginate($perPage);
    }

    public function allAktif(): Collection
    {
        return $this->query()->aktif()->orderBy('nama')->get();
    }
}
