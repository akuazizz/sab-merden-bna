<?php

namespace App\Modules\Shared\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    /**
     * Ambil semua record.
     */
    public function all(): Collection;

    /**
     * Ambil record berdasarkan ID.
     */
    public function find(int $id): ?Model;

    /**
     * Ambil record berdasarkan ID, atau lempar ModelNotFoundException.
     */
    public function findOrFail(int $id): Model;

    /**
     * Buat record baru.
     */
    public function create(array $data): Model;

    /**
     * Update record berdasarkan ID.
     */
    public function update(int $id, array $data): Model;

    /**
     * Hapus record berdasarkan ID (soft delete jika model mendukung).
     */
    public function delete(int $id): bool;

    /**
     * Ambil record dengan paginasi.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
