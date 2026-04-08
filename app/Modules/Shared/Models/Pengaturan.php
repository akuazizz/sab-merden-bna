<?php

namespace App\Modules\Shared\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Pengaturan — key-value store konfigurasi sistem.
 *
 * Tidak menggunakan timestamps (hanya updated_at).
 * Gunakan Pengaturan::get() untuk akses cepat dengan caching.
 *
 * Contoh penggunaan:
 *   $harga = Pengaturan::get('harga_per_kubik');          // string
 *   $harga = Pengaturan::getFloat('harga_per_kubik');     // float
 *   Pengaturan::set('harga_per_kubik', '3000');
 */
class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    /**
     * Tidak ada created_at pada tabel ini.
     */
    const CREATED_AT = null;

    protected $fillable = [
        'kunci',
        'nilai',
        'deskripsi',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    // ── Static Helpers ───────────────────────────────────────────────

    /**
     * Ambil nilai pengaturan berdasarkan kunci.
     * Returns null jika kunci tidak ditemukan.
     */
    public static function get(string $kunci, mixed $default = null): ?string
    {
        $record = static::where('kunci', $kunci)->first();
        return $record ? $record->nilai : $default;
    }

    /**
     * Ambil nilai sebagai float.
     */
    public static function getFloat(string $kunci, float $default = 0.0): float
    {
        return (float) static::get($kunci, $default);
    }

    /**
     * Ambil nilai sebagai integer.
     */
    public static function getInt(string $kunci, int $default = 0): int
    {
        return (int) static::get($kunci, $default);
    }

    /**
     * Set atau update nilai pengaturan.
     */
    public static function set(string $kunci, string $nilai): self
    {
        return static::updateOrCreate(
            ['kunci' => $kunci],
            ['nilai' => $nilai, 'updated_at' => now()]
        );
    }

    /**
     * Ambil semua pengaturan sebagai key-value array.
     * Cocok untuk cache sekaligus.
     */
    public static function allAsArray(): array
    {
        return static::pluck('nilai', 'kunci')->toArray();
    }
}
