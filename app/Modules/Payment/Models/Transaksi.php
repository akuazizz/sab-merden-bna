<?php

namespace App\Modules\Payment\Models;

use App\Modules\Payment\Enums\TransaksiStatus;
use App\Modules\Pelanggan\Models\Pelanggan;
use App\Modules\Tagihan\Models\Tagihan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'tagihan_id',
        'pelanggan_id',
        'kode_transaksi',
        'metode_pembayaran',
        'jumlah',
        'status',
        'snap_token',
        'snap_redirect_url',
        'midtrans_transaction_id',
        'paid_at',
        'expired_at',
        'raw_response',
    ];

    protected $casts = [
        'jumlah'       => 'decimal:2',
        'status'       => TransaksiStatus::class,
        'paid_at'      => 'datetime',
        'expired_at'   => 'datetime',
        'raw_response' => 'array',   // JSON → PHP array otomatis
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * Semua log webhook Midtrans yang terkait transaksi ini.
     */
    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class, 'transaksi_id');
    }

    // ── Accessors ────────────────────────────────────────────────────

    /**
     * Apakah transaksi ini sudah expired?
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expired_at && now()->isAfter($this->expired_at)
            && $this->status === TransaksiStatus::Pending;
    }

    /**
     * Metode pembayaran yang sudah diformat (title_case).
     * Contoh: 'bank_transfer' → 'Bank Transfer'
     */
    public function getMetodeLabelAttribute(): string
    {
        return $this->metode_pembayaran
            ? ucwords(str_replace('_', ' ', $this->metode_pembayaran))
            : '-';
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', TransaksiStatus::Pending);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', TransaksiStatus::Success);
    }

    /**
     * Pending yang belum expired — untuk cek transaksi aktif.
     */
    public function scopePendingAktif($query)
    {
        return $query->where('status', TransaksiStatus::Pending)
            ->where('expired_at', '>', now());
    }
}
