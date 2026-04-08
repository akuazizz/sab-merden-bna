<?php

namespace App\Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    protected $table = 'payment_logs';

    /**
     * Kolom updated_at tidak ada — tabel ini append-only.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'transaksi_id',
        'kode_transaksi',
        'event_type',
        'status_raw',
        'fraud_status',
        'payload',
        'ip_address',
        'is_processed',
        'processed_at',
        'error_message',
    ];

    protected $casts = [
        'payload'      => 'array',   // JSON → PHP array
        'is_processed' => 'boolean',
        'processed_at' => 'datetime',
        'created_at'   => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Transaksi yang terkait (nullable — bisa null jika order_id tidak ditemukan).
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    // ── Accessors ────────────────────────────────────────────────────

    /**
     * Status raw dalam format yang lebih readable.
     * Contoh: 'settlement' → 'Settlement'
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status_raw
            ? ucfirst(str_replace('_', ' ', $this->status_raw))
            : '-';
    }

    /**
     * Apakah ini callback dari Midtrans (bukan manual admin)?
     */
    public function getIsWebhookAttribute(): bool
    {
        return $this->event_type === 'notification';
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    public function scopeByKode($query, string $kode)
    {
        return $query->where('kode_transaksi', $kode);
    }
}
