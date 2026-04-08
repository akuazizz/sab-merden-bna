<?php

namespace App\Modules\Payment\Services;

use App\Modules\Payment\Enums\TransaksiStatus;
use App\Modules\Payment\Exceptions\MidtransSignatureInvalidException;
use App\Modules\Payment\Exceptions\PaymentAlreadyProcessedException;
use App\Modules\Payment\Models\PaymentLog;
use App\Modules\Payment\Models\Transaksi;
use App\Modules\Payment\Repositories\TransaksiRepository;
use App\Modules\Shared\Contracts\EventPublisherInterface;
use App\Modules\Shared\Models\EventLog;
use App\Modules\Shared\Repositories\PengaturanRepository;
use App\Modules\Tagihan\Enums\TagihanStatus;
use App\Modules\Tagihan\Models\Tagihan;
use App\Modules\Tagihan\Services\TagihanService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    // Midtrans Snap API endpoint
    private const SNAP_URL_SANDBOX    = 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    private const SNAP_URL_PRODUCTION = 'https://app.midtrans.com/snap/v1/transactions';

    public function __construct(
        private readonly TransaksiRepository $transaksiRepo,
        private readonly TagihanService      $tagihanService,
        private readonly PengaturanRepository $pengaturan,
        private readonly EventPublisherInterface $publisher,
    ) {}

    // ── Initiate Payment ─────────────────────────────────────────────

    /**
     * Inisiasi pembayaran baru.
     *
     * Flow:
     * 1. Guard: tagihan harus bisa dibayar (status enum)
     * 2. Guard: tidak ada transaksi pending aktif (idempotency layer 1)
     * 3. Buat transaksi dengan status 'pending'
     * 4. Request Snap Token ke Midtrans
     * 5. Update transaksi dengan snap_token + expired_at
     * 6. Return transaksi (controller handle redirect ke Snap)
     */
    public function initiate(Tagihan $tagihan, int $pelangganId): Transaksi
    {
        // ── Guard 1: status tagihan harus bisabayar ───────────────────
        if (!$tagihan->status->isBisaBayar()) {
            throw new \RuntimeException(
                "Tagihan dengan status '{$tagihan->status->label()}' tidak dapat dibayar.",
                422
            );
        }

        // ── Guard 2: idempotency — cek transaksi pending aktif ────────
        // Jika sudah ada transaksi pending DENGAN snap_token → resume
        // (user menutup popup Midtrans, lalu klik Bayar lagi)
        $existingPending = $this->transaksiRepo->findActivePendingByTagihan($tagihan->id);

        if ($existingPending) {
            // Sudah punya snap_token yang valid → kembalikan langsung
            // sehingga frontend bisa re-open Snap popup
            if ($existingPending->snap_token) {
                return $existingPending;
            }
            // Ada pending tapi belum dapat snap_token (stuck) → error
            throw new \RuntimeException(
                'Pembayaran sedang dalam proses. Harap tunggu beberapa saat.',
                409
            );
        }

        return DB::transaction(function () use ($tagihan, $pelangganId) {
            // ── Buat kode transaksi unik ──────────────────────────────
            $kodeTransaksi = $this->generateKodeTransaksi();

            // ── Simpan transaksi pending ──────────────────────────────
            /** @var Transaksi $transaksi */
            $transaksi = $this->transaksiRepo->create([
                'tagihan_id'      => $tagihan->id,
                'pelanggan_id'    => $pelangganId,
                'kode_transaksi'  => $kodeTransaksi,
                'jumlah'          => $tagihan->total_tagihan,
                'status'          => TransaksiStatus::Pending->value,
                'expired_at'      => now()->addHours(24),
            ]);

            // ── Request Snap Token ke Midtrans ────────────────────────
            $snapData = $this->requestSnapToken($transaksi, $tagihan);

            // ── Update transaksi dengan snap token ────────────────────
            $transaksi = $this->transaksiRepo->update($transaksi->id, [
                'snap_token'        => $snapData['token'],
                'snap_redirect_url' => $snapData['redirect_url'],
            ]);

            return $transaksi;
        });
    }

    // ── Process Callback ─────────────────────────────────────────────

    /**
     * Proses notifikasi webhook dari Midtrans.
     * Dipanggil oleh ProcessPaymentCallbackJob (async).
     *
     * Idempotency 3 layer:
     * L1: Transaksi sudah terminal → skip
     * L2: PaymentLog event_type ini sudah processed → skip
     * L3: Pessimistic lock (lockForUpdate) saat update transaksi
     *
     * @param  array $payload  Raw webhook payload dari Midtrans
     * @param  int   $paymentLogId  ID payment_log yang sudah di-insert
     */
    public function processCallback(array $payload, int $paymentLogId): void
    {
        $kodeTransaksi = $payload['order_id'];
        $txStatus      = $payload['transaction_status'];
        $fraudStatus   = $payload['fraud_status'] ?? 'accept';

        DB::transaction(function () use ($kodeTransaksi, $txStatus, $fraudStatus, $payload, $paymentLogId) {

            // ── L3: Pessimistic lock pada transaksi ───────────────────
            $transaksi = $this->transaksiRepo->findByKodeForUpdate($kodeTransaksi);

            if (!$transaksi) {
                Log::error("PaymentService: Transaksi {$kodeTransaksi} tidak ditemukan.");
                $this->markLogFailed($paymentLogId, 'Transaksi tidak ditemukan');
                return;
            }

            // ── L1: Idempotency — skip jika sudah terminal ────────────
            if ($transaksi->status->isTerminal()) {
                Log::info("PaymentService: Transaksi {$kodeTransaksi} sudah terminal ({$transaksi->status->value}). Skip.");
                $this->markLogProcessed($paymentLogId);
                return;
            }

            // ── Mapping status Midtrans → TransaksiStatus ─────────────
            $statusBaru = TransaksiStatus::fromMidtrans($txStatus, $fraudStatus);

            // ── Update Transaksi ──────────────────────────────────────
            $updateData = [
                'status'                   => $statusBaru->value,
                'midtrans_transaction_id'  => $payload['transaction_id'] ?? null,
                'metode_pembayaran'        => $payload['payment_type'] ?? null,
                'raw_response'             => $payload,
            ];

            if ($statusBaru === TransaksiStatus::Success) {
                $updateData['paid_at'] = now();
            }

            $this->transaksiRepo->update($transaksi->id, $updateData);

            // ── Update Tagihan berdasarkan status transaksi ───────────
            $tagihan = Tagihan::lockForUpdate()->find($transaksi->tagihan_id);

            if ($tagihan && !$tagihan->status->isTerminal()) {
                $statusTagihanBaru = $this->mapTransaksiToTagihanStatus($statusBaru, $tagihan->status);
                if ($statusTagihanBaru) {
                    $this->tagihanService->transisiStatus($tagihan, $statusTagihanBaru);
                }
            }

            // ── Mark payment_log sebagai processed ───────────────────
            $this->markLogProcessed($paymentLogId);

            // ── Publish ke RabbitMQ (AMQP) ───────────────────────────
            $this->publisher->publish(
                exchange:   config('rabbitmq.exchanges.payment', 'sab.payment'),
                routingKey: config('rabbitmq.routing_keys.PaymentCallback', 'payment.callback'),
                payload:    [
                    'kode_transaksi' => $kodeTransaksi,
                    'tx_status'      => $txStatus,
                    'status_baru'    => $statusBaru->value,
                    'fraud_status'   => $fraudStatus,
                ],
            );

            // ── Catat ke event_logs ───────────────────────────────────
            EventLog::catat(
                eventName:     "PaymentCallback:{$txStatus}",
                aggregateType: 'Transaksi',
                aggregateId:   $transaksi->id,
                payload: [
                    'kode_transaksi' => $kodeTransaksi,
                    'tx_status'      => $txStatus,
                    'status_baru'    => $statusBaru->value,
                    'fraud_status'   => $fraudStatus,
                ],
            );
        });
    }

    // ── Signature Validation ─────────────────────────────────────────

    /**
     * Validasi signature Midtrans.
     * signature_key = SHA512(order_id + status_code + gross_amount + server_key)
     */
    public function validateSignature(array $payload): void
    {
        $serverKey  = config('services.midtrans.server_key');
        $orderId    = $payload['order_id']    ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmt   = $payload['gross_amount'] ?? '';

        $expected = hash('sha512', $orderId . $statusCode . $grossAmt . $serverKey);

        if (!hash_equals($expected, $payload['signature_key'] ?? '')) {
            throw new MidtransSignatureInvalidException();
        }
    }

    // ── Midtrans API ─────────────────────────────────────────────────

    /**
     * Request Snap Token ke Midtrans API.
     * Menggunakan HTTP client Laravel (tidak butuh SDK).
     */
    private function requestSnapToken(Transaksi $transaksi, Tagihan $tagihan): array
    {
        $isProduction = config('services.midtrans.is_production', false);
        $serverKey    = config('services.midtrans.server_key');
        $snapUrl      = $isProduction ? self::SNAP_URL_PRODUCTION : self::SNAP_URL_SANDBOX;

        $pelanggan    = $tagihan->pelanggan;

        $body = [
            'transaction_details' => [
                'order_id'     => $transaksi->kode_transaksi,
                'gross_amount' => (int) $transaksi->jumlah,
            ],
            'customer_details' => [
                'first_name' => $pelanggan->nama,
                'phone'      => $pelanggan->telepon ?? '',
            ],
            'item_details' => [
                [
                    'id'       => "tagihan-{$tagihan->id}",
                    'price'    => (int) $tagihan->total_tagihan,
                    'quantity' => 1,
                    'name'     => "Tagihan Air {$tagihan->periode}",
                ],
            ],
            'expiry' => [
                'unit'     => 'hours',
                'duration' => 24,
            ],
        ];

        $response = \Illuminate\Support\Facades\Http::withBasicAuth($serverKey, '')
            ->timeout(30)
            ->post($snapUrl, $body);

        if (!$response->successful()) {
            Log::error('Midtrans Snap error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Gagal mendapatkan Snap Token dari Midtrans.', 502);
        }

        return $response->json();
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Map TransaksiStatus → TagihanStatus yang sesuai.
     * Hanya status yang relevan yang dipetakan.
     */
    private function mapTransaksiToTagihanStatus(
        TransaksiStatus $transaksiStatus,
        TagihanStatus   $currentTagihanStatus,
    ): ?TagihanStatus {
        return match ($transaksiStatus) {
            TransaksiStatus::Success   => TagihanStatus::Lunas,
            // Untuk partial payment, bisa dikembangkan ke Sebagian
            // TransaksiStatus::Partial  => TagihanStatus::Sebagian,
            default                    => null, // pending/failed/cancelled tidak ubah tagihan
        };
    }

    private function generateKodeTransaksi(): string
    {
        return 'SAB-' . strtoupper(Str::random(12));
    }

    private function markLogProcessed(int $paymentLogId): void
    {
        PaymentLog::where('id', $paymentLogId)->update([
            'is_processed' => true,
            'processed_at' => now(),
        ]);
    }

    private function markLogFailed(int $paymentLogId, string $errorMessage): void
    {
        PaymentLog::where('id', $paymentLogId)->update([
            'is_processed'  => false,
            'error_message' => $errorMessage,
        ]);
    }
}
