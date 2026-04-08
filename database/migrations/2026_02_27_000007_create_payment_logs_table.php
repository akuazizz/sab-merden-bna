<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel payment_logs — audit trail immutable semua notifikasi dari Midtrans.
 *
 * Prinsip desain:
 * - APPEND-ONLY: tidak pernah di-DELETE, hanya is_processed yang di-UPDATE
 * - Selalu INSERT bahkan untuk callback duplikat (audit trail harus lengkap)
 * - transaksi_id nullable karena callback bisa datang dengan order_id tidak dikenal
 * - payload menyimpan FULL raw webhook body sebagai JSON
 *
 * Tabel ini adalah sumber kebenaran untuk debugging masalah pembayaran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();

            // Nullable: di-resolve oleh consumer setelah lookup berdasarkan kode_transaksi
            $table->foreignId('transaksi_id')
                  ->nullable()
                  ->constrained('transaksi')
                  ->nullOnDelete();

            // order_id dari payload Midtrans — disimpan langsung dari webhook
            $table->string('kode_transaksi', 50)->nullable();

            // Tipe event: 'notification' (webhook Midtrans), 'manual' (admin override)
            $table->string('event_type', 50);

            // transaction_status mentah dari Midtrans: 'settlement', 'pending', 'deny', dll
            $table->string('status_raw', 50)->nullable();

            // Hasil fraud check Midtrans: 'accept', 'deny', 'challenge'
            $table->string('fraud_status', 20)->nullable();

            // FULL raw webhook body — wajib ada untuk audit dan debugging
            $table->json('payload');

            $table->string('ip_address', 45)->nullable();

            // Flag apakah consumer sudah memproses log ini
            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();

            // Pesan error jika consumer gagal memproses
            $table->text('error_message')->nullable();

            // Hanya created_at — tidak ada updated_at (append-only pattern)
            $table->timestamp('created_at')->nullable();

            // ── Index ─────────────────────────────────────────────────
            $table->index('transaksi_id', 'idx_payment_logs_transaksi');
            $table->index('kode_transaksi', 'idx_payment_logs_kode');

            // Index untuk consumer: cari log yang belum diproses
            $table->index(['is_processed', 'created_at'], 'idx_payment_logs_unprocessed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
