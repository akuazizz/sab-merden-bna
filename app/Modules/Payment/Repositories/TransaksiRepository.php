<?php

namespace App\Modules\Payment\Repositories;

use App\Modules\Payment\Models\PaymentLog;
use App\Modules\Payment\Models\Transaksi;
use App\Modules\Shared\Repositories\BaseRepository;

class TransaksiRepository extends BaseRepository
{
    protected string $model = Transaksi::class;

    public function findByKode(string $kode): ?Transaksi
    {
        return $this->query()->where('kode_transaksi', $kode)->first();
    }

    /**
     * Pessimistic lock — dipakai di dalam DB::transaction saat callback.
     */
    public function findByKodeForUpdate(string $kode): ?Transaksi
    {
        return $this->query()
            ->where('kode_transaksi', $kode)
            ->lockForUpdate()
            ->first();
    }

    public function findBySnapToken(string $token): ?Transaksi
    {
        return $this->query()->where('snap_token', $token)->first();
    }

    public function countPendingByTagihan(int $tagihanId): int
    {
        return $this->query()
            ->where('tagihan_id', $tagihanId)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->count();
    }

    /**
     * Ambil transaksi pending aktif (belum expired) untuk tagihan ini.
     * Digunakan untuk resume snap_token jika user menutup popup.
     */
    public function findActivePendingByTagihan(int $tagihanId): ?Transaksi
    {
        return $this->query()
            ->where('tagihan_id', $tagihanId)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->latest()
            ->first();
    }


    public function insertPaymentLog(array $data): PaymentLog
    {
        return PaymentLog::create($data);
    }

    /**
     * Cek apakah payment_log untuk kode transaksi ini sudah diproses.
     * Layer 3 idempotency: cek event_type yang sudah diproses.
     */
    public function isPaymentLogProcessed(string $kode, string $eventType): bool
    {
        return PaymentLog::where('kode_transaksi', $kode)
            ->where('event_type', $eventType)
            ->where('is_processed', true)
            ->exists();
    }
}
