<?php

namespace App\Modules\Shared\Repositories;

use App\Modules\Shared\Models\Pengaturan;

/**
 * PengaturanRepository — typed access ke tabel pengaturan dengan caching.
 *
 * Caching menggunakan Laravel Cache dengan TTL yang bisa dikonfigurasi.
 * Cache di-flush saat admin mengubah pengaturan.
 */
class PengaturanRepository
{
    private const CACHE_KEY = 'pengaturan.all';
    private const CACHE_TTL = 3600; // 1 jam

    /**
     * Ambil nilai string dengan cache.
     */
    public function get(string $kunci, mixed $default = null): ?string
    {
        return $this->all()[$kunci] ?? $default;
    }

    public function getFloat(string $kunci, float $default = 0.0): float
    {
        return (float) $this->get($kunci, $default);
    }

    public function getInt(string $kunci, int $default = 0): int
    {
        return (int) $this->get($kunci, $default);
    }

    /**
     * Ambil semua pengaturan sebagai array — dengan cache.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return \Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Pengaturan::pluck('nilai', 'kunci')->toArray();
        });
    }

    /**
     * Set pengaturan dan invalidate cache.
     */
    public function set(string $kunci, string $nilai): Pengaturan
    {
        $record = Pengaturan::updateOrCreate(
            ['kunci' => $kunci],
            ['nilai' => $nilai, 'updated_at' => now()]
        );

        \Cache::forget(self::CACHE_KEY);

        return $record;
    }

    /**
     * Invalidate cache secara manual (dipanggil setelah bulk update).
     */
    public function clearCache(): void
    {
        \Cache::forget(self::CACHE_KEY);
    }
}
