<?php
/**
 * Script simulasi webhook Midtrans untuk testing lokal.
 * Jalankan SETELAH melakukan pembayaran di Snap popup.
 *
 * Usage: php simulate_webhook.php [kode_transaksi]
 * Contoh: php simulate_webhook.php SAB-Q5SFAMSYCHV1
 */
define('LARAVEL_START', microtime(true));
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Modules\Payment\Models\Transaksi;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Payment\Models\PaymentLog;

// Ambil transaksi pending terbaru jika tidak ada argumen
$kodeTransaksi = $argv[1] ?? null;

if (!$kodeTransaksi) {
    $transaksi = Transaksi::where('status', 'pending')
        ->whereNotNull('snap_token')
        ->latest()
        ->first();

    if (!$transaksi) {
        echo "❌ Tidak ada transaksi pending. Lakukan klik 'Bayar Sekarang' dulu.\n";
        exit(1);
    }
    $kodeTransaksi = $transaksi->kode_transaksi;
}

echo "🔄 Simulasi webhook SUCCESS untuk transaksi: {$kodeTransaksi}\n";

// Buat signature yang valid
$serverKey   = config('services.midtrans.server_key');
$orderId     = $kodeTransaksi;
$statusCode  = '200';
$grossAmount = '42500.00';

$signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

// Payload webhook Midtrans (format asli)
$payload = [
    'transaction_time'   => now()->format('Y-m-d H:i:s'),
    'transaction_status' => 'settlement',   // ← status SUKSES
    'transaction_id'     => \Illuminate\Support\Str::uuid()->toString(),
    'status_code'        => $statusCode,
    'signature_key'      => $signature,
    'payment_type'       => 'qris',
    'order_id'           => $orderId,
    'merchant_id'        => 'M406195994',
    'gross_amount'       => $grossAmount,
    'fraud_status'       => 'accept',
    'currency'           => 'IDR',
];

try {
    // Insert payment_log
    $paymentLog = PaymentLog::create([
        'transaksi_id'   => null,
        'kode_transaksi' => $orderId,
        'event_type'     => 'notification-simulated',
        'status_raw'     => 'settlement',
        'fraud_status'   => 'accept',
        'payload'        => $payload,
        'ip_address'     => '127.0.0.1',
        'is_processed'   => false,
    ]);

    echo "✓ PaymentLog ID: {$paymentLog->id} dibuat\n";

    // Process callback langsung (bypass HTTP)
    $service = app(PaymentService::class);
    $service->processCallback($payload, $paymentLog->id);

    echo "✓ Callback diproses!\n";

    // Cek hasil
    $transaksiUpdate = Transaksi::where('kode_transaksi', $orderId)->first();
    $tagihan         = $transaksiUpdate?->tagihan;

    echo "\n=== HASIL ===\n";
    echo "Transaksi : {$transaksiUpdate?->status?->value}\n";
    echo "Tagihan   : {$tagihan?->status?->value} | {$tagihan?->nomor_tagihan}\n";

    if ($tagihan?->status?->value === 'lunas') {
        echo "\n🎉 SUKSES! Tagihan sudah LUNAS!\n";
    }

} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
