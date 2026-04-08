<?php

namespace App\Modules\Payment\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Exceptions\MidtransSignatureInvalidException;
use App\Modules\Payment\Jobs\ProcessPaymentCallbackJob;
use App\Modules\Payment\Models\PaymentLog;
use App\Modules\Payment\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * MidtransWebhookController — menerima POST callback dari Midtrans.
 *
 * Design prinsip:
 * 1. SELALU return HTTP 200 ke Midtrans (agar tidak di-retry paksa)
 * 2. Insert payment_log DULU (append-only audit) sebelum proses apapun
 * 3. Dispatch ProcessPaymentCallbackJob ke queue (async)
 * 4. Validation bypass CSRF — endpoint ini public (dikecualikan di VerifyCsrfToken)
 *
 * Route: POST /webhook/midtrans (tanpa auth middleware)
 */
class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        // ── Log raw request untuk debugging ──────────────────────────
        Log::channel('daily')->info('Midtrans webhook received', [
            'order_id' => $payload['order_id'] ?? 'unknown',
            'status'   => $payload['transaction_status'] ?? 'unknown',
            'ip'       => $request->ip(),
        ]);

        // ── Step 1: Validasi signature Midtrans ───────────────────────
        try {
            $this->paymentService->validateSignature($payload);
        } catch (MidtransSignatureInvalidException $e) {
            Log::warning('Midtrans: signature invalid', ['ip' => $request->ip(), 'payload' => $payload]);
            // Tetap return 200 agar Midtrans tidak retry — ini bukan error sementara
            return response()->json(['message' => 'Signature invalid'], 200);
        }

        // ── Step 2: Insert payment_log (immutable audit) ──────────────
        // Ini HARUS berhasil sebelum dispatch job.
        // Jika insert gagal, return 500 → Midtrans akan retry.
        $paymentLog = PaymentLog::create([
            'transaksi_id'  => null, // akan di-resolve oleh job
            'kode_transaksi'=> $payload['order_id']           ?? '',
            'event_type'    => 'notification',
            'status_raw'    => $payload['transaction_status'] ?? '',
            'fraud_status'  => $payload['fraud_status']       ?? null,
            'payload'       => $payload,
            'ip_address'    => $request->ip(),
            'is_processed'  => false,
        ]);

        // ── Step 3: Dispatch job ke queue ─────────────────────────────
        ProcessPaymentCallbackJob::dispatch($payload, $paymentLog->id)
            ->onQueue('payment');

        // ── Step 4: Selalu return 200 ke Midtrans ────────────────────
        return response()->json(['message' => 'OK', 'log_id' => $paymentLog->id]);
    }
}
