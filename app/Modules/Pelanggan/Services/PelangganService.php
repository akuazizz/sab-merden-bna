<?php

namespace App\Modules\Pelanggan\Services;

use App\Modules\Pelanggan\Enums\PelangganStatus;
use App\Modules\Pelanggan\Events\WargaTerdaftar;
use App\Modules\Pelanggan\Exceptions\PelangganMasihMemilikiTagihanException;
use App\Modules\Pelanggan\Exceptions\PelangganNonaktifException;
use App\Modules\Pelanggan\Exceptions\PelangganNotFoundException;
use App\Modules\Pelanggan\Models\Pelanggan;
use App\Modules\Pelanggan\Repositories\PelangganRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PelangganService
{
    public function __construct(
        private readonly PelangganRepository $repo,
    ) {}

    // ── Create ───────────────────────────────────────────────────────

    public function create(array $data): Pelanggan
    {
        return DB::transaction(function () use ($data) {
            // 1. Buat akun User untuk pelanggan
            $user = \App\Models\User::create([
                'name'     => $data['nama'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
                'is_active'=> true,
            ]);

            // 2. Assign role pelanggan
            $user->assignRole('pelanggan');

            // 3. Buat data pelanggan, link ke user
            $data['nomor_pelanggan'] = $this->generateNomorPelanggan();
            $data['tanggal_daftar']  ??= now()->toDateString();
            $data['status']          ??= PelangganStatus::Aktif->value;
            $data['user_id']         = $user->id;

            // Buang field yang bukan kolom pelanggan
            unset($data['email'], $data['password'], $data['password_confirmation']);

            $pelanggan = $this->repo->create($data);

            event(new WargaTerdaftar($pelanggan));

            return $pelanggan;
        });
    }

    // ── Read ─────────────────────────────────────────────────────────

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage);
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->search($keyword, $perPage);
    }

    public function findOrFail(int $id): Pelanggan
    {
        $pelanggan = $this->repo->find($id);

        if (!$pelanggan) {
            throw new PelangganNotFoundException($id);
        }

        return $pelanggan;
    }

    public function findByNomorOrFail(string $nomor): Pelanggan
    {
        $pelanggan = $this->repo->findByNomor($nomor);

        if (!$pelanggan) {
            throw new PelangganNotFoundException($nomor);
        }

        return $pelanggan;
    }

    // ── Update ───────────────────────────────────────────────────────

    public function update(int $id, array $data): Pelanggan
    {
        // nomor_pelanggan tidak boleh di-update manual
        unset($data['nomor_pelanggan']);

        return DB::transaction(function () use ($id, $data) {
            $pelanggan = $this->findOrFail($id);
            return $this->repo->update($pelanggan->id, $data);
        });
    }

    // ── Deactivate / Activate ────────────────────────────────────────

    /**
     * Nonaktifkan pelanggan.
     * Guard: tolak jika masih ada tagihan aktif.
     */
    public function deactivate(int $id): Pelanggan
    {
        return DB::transaction(function () use ($id) {
            $pelanggan = $this->findOrFail($id);

            if ($pelanggan->status === PelangganStatus::Nonaktif) {
                return $pelanggan; // idempotent — sudah nonaktif
            }

            if ($this->repo->hasTagihanAktif($id)) {
                throw new PelangganMasihMemilikiTagihanException();
            }

            return $this->repo->update($id, [
                'status' => PelangganStatus::Nonaktif->value,
            ]);
        });
    }

    public function activate(int $id): Pelanggan
    {
        $pelanggan = $this->findOrFail($id);

        if ($pelanggan->status === PelangganStatus::Aktif) {
            return $pelanggan; // idempotent
        }

        return $this->repo->update($id, [
            'status' => PelangganStatus::Aktif->value,
        ]);
    }

    // ── Delete ───────────────────────────────────────────────────────

    /**
     * Soft delete pelanggan.
     * Guard: tolak jika masih ada tagihan aktif.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $pelanggan = $this->findOrFail($id);

            if ($this->repo->hasTagihanAktif($pelanggan->id)) {
                throw new PelangganMasihMemilikiTagihanException();
            }

            return $this->repo->delete($pelanggan->id);
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Generate nomor pelanggan unik: SAB-YYYYNNN
     * Contoh: SAB-2024001, SAB-2024015
     */
    public function generateNomorPelanggan(): string
    {
        $tahun  = now()->year;
        $urutan = $this->repo->countByTahun($tahun) + 1;

        return sprintf('SAB-%d%03d', $tahun, $urutan);
    }

    /**
     * Pastikan pelanggan aktif — dipakai oleh service lain (MeteranService).
     */
    public function assertAktif(Pelanggan $pelanggan): void
    {
        if ($pelanggan->status !== PelangganStatus::Aktif) {
            throw new PelangganNonaktifException($pelanggan->nama);
        }
    }
}
