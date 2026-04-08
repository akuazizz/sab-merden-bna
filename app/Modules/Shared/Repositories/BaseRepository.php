<?php

namespace App\Modules\Shared\Repositories;

use App\Modules\Shared\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Implementasi dasar RepositoryInterface menggunakan Eloquent.
 *
 * Setiap repository konkret harus:
 * 1. Extend class ini
 * 2. Set properti $model dengan class Model yang sesuai
 *
 * Contoh:
 *   class WargaRepository extends BaseRepository
 *   {
 *       protected string $model = Warga::class;
 *   }
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * Nama class Model Eloquent yang dikelola.
     * Wajib di-override di child class.
     */
    protected string $model;

    protected Model $instance;

    public function __construct()
    {
        $this->instance = app($this->model);
    }

    public function all(): Collection
    {
        return $this->instance->newQuery()->get();
    }

    public function find(int $id): ?Model
    {
        return $this->instance->newQuery()->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->instance->newQuery()->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->instance->newQuery()->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);
        return (bool) $record->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->instance->newQuery()->paginate($perPage);
    }

    /**
     * Helper untuk mendapatkan query builder baru.
     * Berguna untuk membangun query kustom di child class.
     */
    protected function query()
    {
        return $this->instance->newQuery();
    }
}
