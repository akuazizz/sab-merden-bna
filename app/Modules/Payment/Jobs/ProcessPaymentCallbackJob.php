<?php

namespace App\Modules\Payment\Jobs;

use App\Modules\Payment\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk memproses callback Midtrans secara asynchronous.
 *
 * Design:
 * - Dipanggil SETELAH PaymentLog di-insert (WebhookController sudah return 200 ke Midtrans)
 * - Queue: 'payment' (prioritas tinggi, worker dedicated)
 * - Retry: 3x dengan backoff 30s, 60s, 120s
 * - Failed job → failed_jobs table (Laravel default)
 *
 * Idempotency dijaga di PaymentService::processCallback() — aman di-retry.
 */
class ProcessPaymentCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah retry maksimal sebelum job masuk failed_jobs.
     */
    public int $tries = 3;

    /**
     * Timeout per attempt (detik).
     */
    public int $timeout = 60;

    /**
     * Backoff antara retry: 30s, 60s, 120s.
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function __construct(
        private readonly array $payload,
        private readonly int   $paymentLogId,
    ) {}

    public function handle(PaymentService $service): void
    {
        Log::info('ProcessPaymentCallbackJob: memproses', [
            'order_id'       => $this->payload['order_id'] ?? '?',
            'payment_log_id' => $this->paymentLogId,
            'attempt'        => $this->attempts(),
        ]);

        $service->processCallback($this->payload, $this->paymentLogId);
    }

    /**
     * Hook saat job masuk failed_jobs (setelah semua retry habis).
     */
    public function failed(\Throwable $e): void
    {
        Log::error('ProcessPaymentCallbackJob: GAGAL setelah max retry', [
            'order_id'       => $this->payload['order_id'] ?? '?',
            'payment_log_id' => $this->paymentLogId,
            'error'          => $e->getMessage(),
        ]);
    }
}
