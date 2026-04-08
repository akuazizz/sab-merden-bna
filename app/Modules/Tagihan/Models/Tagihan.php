<?php

namespace App\Modules\Tagihan\Models;

use App\Modules\Meteran\Models\MeteranReading;
use App\Modules\Payment\Models\Transaksi;
use App\Modules\Pelanggan\Models\Pelanggan;
use App\Modules\Tagihan\Enums\TagihanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tagihan extends Model
{
    protected $table = 'tagihan';

    protected $fillable = [
        'pelanggan_id',
        'meter_reading_id',
        'nomor_tagihan',
        'periode',
        'pemakaian_kubik',
        'harga_per_kubik',
        'biaya_admin',
        'denda',
        'total_tagihan',
        'status',
        'tanggal_terbit',
        'tanggal_jatuh_tempo',
        'tanggal_lunas',
        'catatan',
    ];

    protected $casts = [
        'pemakaian_kubik'    => 'decimal:2',
        'harga_per_kubik'    => 'decimal:2',
        'biaya_admin'        => 'decimal:2',
        'denda'              => 'decimal:2',
        'total_tagihan'      => 'decimal:2',
        'status'             => TagihanStatus::class,
        'tanggal_terbit'     => 'date',
        'tanggal_jatuh_tempo'=> 'date',
        'tanggal_lunas'      => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * Meter reading yang menghasilkan tagihan ini (1:1).
     */
    public function meterReading(): BelongsTo
    {
        return $this->belongsTo(MeteranReading::class, 'meter_reading_id');
    }

    /**
     * Semua percobaan transaksi pembayaran untuk tagihan ini.
     */
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'tagihan_id');
    }

    /**
     * Transaksi yang sukses (seharusnya max 1).
     */
    public function transaksiSukses(): HasOne
    {
        return $this->hasOne(Transaksi::class, 'tagihan_id')
            ->where('status', 'success');
    }

    /**
     * Transaksi pending aktif (belum expired).
     */
    public function transaksiPendingAktif(): HasOne
    {
        return $this->hasOne(Transaksi::class, 'tagihan_id')
            ->where('status', 'pending')
            ->where('expired_at', '>', now());
    }

    // ── Accessors ────────────────────────────────────────────────────

    /**
     * Apakah tagihan ini bisa dibayar?
     * Delegasi ke enum — single source of truth.
     */
    public function getBisaBayarAttribute(): bool
    {
        if (!$this->status->isBisaBayar()) {
            return false;
        }

        // Jangan izinkan bayar jika ada transaksi pending aktif
        return !$this->transaksiPendingAktif()->exists();
    }

    /**
     * Apakah tagihan sudah melewati jatuh tempo?
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->tanggal_jatuh_tempo
            && now()->isAfter($this->tanggal_jatuh_tempo)
            && !$this->status->isTerminal();
    }

    /**
     * Label periode yang mudah dibaca: '2024-01' → 'Januari 2024'
     */
    public function getPeriodeLabelAttribute(): string
    {
        [$tahun, $bulan] = explode('-', $this->periode);
        return \Carbon\Carbon::create($tahun, $bulan, 1)
            ->translatedFormat('F Y');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['terbit', 'sebagian', 'jatuh_tempo']);
    }

    public function scopeLunas($query)
    {
        return $query->where('status', TagihanStatus::Lunas);
    }

    public function scopePeriode($query, string $periode)
    {
        return $query->where('periode', $periode);
    }

    public function scopeTahun($query, int $tahun)
    {
        return $query->where('periode', 'like', $tahun . '-%');
    }
}
