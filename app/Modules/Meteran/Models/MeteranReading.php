<?php

namespace App\Modules\Meteran\Models;

use App\Models\User;
use App\Modules\Pelanggan\Models\Pelanggan;
use App\Modules\Tagihan\Models\Tagihan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MeteranReading extends Model
{
    protected $table = 'meter_readings';

    protected $fillable = [
        'pelanggan_id',
        'periode',
        'kubik_awal',
        'kubik_akhir',
        'pemakaian',
        'dicatat_oleh',
        'foto_meteran',
        'catatan',
    ];

    protected $casts = [
        'kubik_awal'  => 'decimal:2',
        'kubik_akhir' => 'decimal:2',
        'pemakaian'   => 'decimal:2',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Pelanggan pemilik meter reading ini.
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * Petugas yang mencatat pembacaan ini.
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    /**
     * Tagihan yang di-generate dari meter reading ini (1:1).
     */
    public function tagihan(): HasOne
    {
        return $this->hasOne(Tagihan::class, 'meter_reading_id');
    }

    // ── Accessors ────────────────────────────────────────────────────

    /**
     * Label periode: '2024-01' → 'Januari 2024'
     */
    public function getPeriodeLabelAttribute(): string
    {
        [$tahun, $bulan] = explode('-', $this->periode);
        return \Carbon\Carbon::create($tahun, $bulan, 1)
            ->translatedFormat('F Y');
    }

    /**
     * Apakah pemakaian melebihi batas normal?
     * Diambil dari config agar tidak hardcode.
     */
    public function isAbnormal(): bool
    {
        $max = (float) config('sab.max_kubik_normal', 20);
        return $this->pemakaian > $max;
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopePeriode($query, string $periode)
    {
        return $query->where('periode', $periode);
    }

    public function scopeBelumDitagih($query)
    {
        // Reading yang belum punya tagihan
        return $query->doesntHave('tagihan');
    }
}
