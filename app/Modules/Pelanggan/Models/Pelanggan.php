<?php

namespace App\Modules\Pelanggan\Models;

use App\Models\User;
use App\Modules\Meteran\Models\MeteranReading;
use App\Modules\Pelanggan\Enums\PelangganStatus;
use App\Modules\Tagihan\Models\Tagihan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use SoftDeletes;

    protected $table = 'pelanggan';

    protected $fillable = [
        'user_id',
        'nomor_pelanggan',
        'nama',
        'nik',
        'alamat',
        'rt',
        'rw',
        'dusun',
        'telepon',
        'status',
        'tanggal_daftar',
    ];

    protected $casts = [
        'status'         => PelangganStatus::class,
        'tanggal_daftar' => 'date',
        'deleted_at'     => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Akun login yang terhubung ke pelanggan ini (opsional, 1:1).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Semua pembacaan meteran pelanggan ini.
     */
    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeteranReading::class, 'pelanggan_id');
    }

    /**
     * Pembacaan meteran bulan tertentu.
     */
    public function meterReadingPeriode(string $periode): ?MeteranReading
    {
        return $this->meterReadings()->where('periode', $periode)->first();
    }

    /**
     * Semua tagihan pelanggan ini.
     */
    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'pelanggan_id');
    }

    /**
     * Tagihan yang masih aktif (belum lunas).
     */
    public function tagihanAktif(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'pelanggan_id')
            ->whereIn('status', ['terbit', 'sebagian', 'jatuh_tempo']);
    }

    // ── Accessors ────────────────────────────────────────────────────

    /**
     * Nama tampilan lengkap: "SAB-2024001 — Budi Santoso"
     */
    public function getNamaLengkapAttribute(): string
    {
        return "{$this->nomor_pelanggan} — {$this->nama}";
    }

    /**
     * Alamat lengkap: "RT 02/RW 03, Dusun Merden"
     */
    public function getAlamatLengkapAttribute(): string
    {
        $parts = [];

        if ($this->rt && $this->rw) {
            $parts[] = "RT {$this->rt}/RW {$this->rw}";
        }

        if ($this->dusun) {
            $parts[] = "Dusun {$this->dusun}";
        }

        $parts[] = $this->alamat;

        return implode(', ', array_filter($parts));
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status', PelangganStatus::Aktif);
    }

    public function scopeNonaktif($query)
    {
        return $query->where('status', PelangganStatus::Nonaktif);
    }
}
