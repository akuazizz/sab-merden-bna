<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel transaksi — setiap percobaan pembayaran sebuah tagihan.
 *
 * Desain penting:
 * - Satu tagihan BOLEH punya banyak transaksi (retry pembayaran)
 * - Hanya boleh ada SATU transaksi 'pending' aktif per tagihan (dijaga di service layer)
 * - kode_transaksi = order_id yang dikirim ke Midtrans (UNIQUE)
 * - raw_response menyimpan full response Midtrans sebagai JSON untuk audit
 *
 * State machine:
 *   pending → success | failed | cancelled | expired
 *   success → refunded
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tagihan_id')
                  ->constrained('tagihan');

            $table->foreignId('pelanggan_id')
                  ->constrained('pelanggan');

            // order_id yang dikirim ke Midtrans — HARUS unik dan immutable
            $table->string('kode_transaksi', 50)->unique();

            // Diisi oleh Midtrans callback: 'bank_transfer', 'gopay', 'credit_card', dll
            $table->string('metode_pembayaran', 50)->nullable();

            // Jumlah yang harus dibayar (snapshot dari tagihan.total_tagihan saat inisiasi)
            $table->decimal('jumlah', 12, 2);

            // ── State Machine ─────────────────────────────────────────
            $table->enum('status', [
                'pending',    // menunggu pembayaran
                'success',    // TERMINAL STATE — pembayaran dikonfirmasi
                'failed',     // pembayaran gagal (bisa retry)
                'cancelled',  // dibatalkan pelanggan atau admin
                'expired',    // melewati waktu expiry (bisa retry)
                'refunded',   // dikembalikan (dari success)
            ])->default('pending');

            // ── Midtrans Specific ─────────────────────────────────────
            $table->string('snap_token', 255)->nullable();
            $table->string('snap_redirect_url', 500)->nullable();

            // transaction_id dari sisi Midtrans (berbeda dengan order_id kita)
            $table->string('midtrans_transaction_id', 100)->nullable();

            // Waktu pembayaran dikonfirmasi oleh Midtrans
            $table->timestamp('paid_at')->nullable();

            // Waktu transaksi expired — diisi saat create, default 24 jam
            $table->timestamp('expired_at')->nullable();

            // Full response Midtrans sebagai audit trail — JSON column
            $table->json('raw_response')->nullable();

            $table->timestamps();

            // ── Index ─────────────────────────────────────────────────
            $table->index('tagihan_id', 'idx_transaksi_tagihan');
            $table->index('pelanggan_id', 'idx_transaksi_pelanggan');
            $table->index('kode_transaksi', 'idx_transaksi_kode');
            $table->index('status', 'idx_transaksi_status');
            $table->index('midtrans_transaction_id', 'idx_transaksi_midtrans_id');

            // Covering index untuk laporan pemasukan (query terbanyak)
            $table->index(
                ['status', 'paid_at', 'jumlah', 'tagihan_id'],
                'idx_transaksi_paid_status'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
