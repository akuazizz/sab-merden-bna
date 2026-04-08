<?php

namespace App\Modules\Payment\Enums;

enum TransaksiStatus: string
{
    case Pending   = 'pending';
    case Success   = 'success';
    case Failed    = 'failed';
    case Cancelled = 'cancelled';
    case Expired   = 'expired';
    case Refunded  = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Menunggu Pembayaran',
            self::Success   => 'Berhasil',
            self::Failed    => 'Gagal',
            self::Cancelled => 'Dibatalkan',
            self::Expired   => 'Kedaluwarsa',
            self::Refunded  => 'Dikembalikan',
        };
    }

    /**
     * Apakah ini terminal state? Terminal state tidak bisa di-update lagi
     * oleh callback kecuali 'success' → 'refunded'.
     */
    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Success,
            self::Failed,
            self::Cancelled,
            self::Expired,
            self::Refunded,
        ]);
    }

    /**
     * Apakah pembayaran mungkin masih bisa dilanjutkan?
     * (transaksi failed/cancelled/expired → bisa buat transaksi baru)
     */
    public function isRetryable(): bool
    {
        return in_array($this, [self::Failed, self::Cancelled, self::Expired]);
    }

    /**
     * Map dari Midtrans transaction_status + fraud_status ke TransaksiStatus.
     */
    public static function fromMidtrans(string $txStatus, string $fraudStatus = 'accept'): self
    {
        return match(true) {
            $txStatus === 'capture'    && $fraudStatus === 'accept'    => self::Success,
            $txStatus === 'capture'    && $fraudStatus === 'challenge' => self::Pending,
            $txStatus === 'settlement'                                 => self::Success,
            $txStatus === 'pending'                                    => self::Pending,
            $txStatus === 'deny'                                       => self::Failed,
            $txStatus === 'cancel'                                     => self::Cancelled,
            $txStatus === 'expire'                                     => self::Expired,
            $txStatus === 'refund'                                     => self::Refunded,
            default                                                    => self::Failed,
        };
    }

    public function badgeColor(): string
    {
        return match($this) {
            self::Pending   => 'yellow',
            self::Success   => 'green',
            self::Failed    => 'red',
            self::Cancelled => 'gray',
            self::Expired   => 'slate',
            self::Refunded  => 'purple',
        };
    }
}
