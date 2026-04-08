<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel event_logs — audit trail semua domain event yang terjadi di sistem.
 *
 * Digunakan untuk:
 * - Observability: track semua event yang pernah terjadi
 * - Debugging: lihat urutan event untuk tracing masalah
 * - Monitoring: admin bisa lihat event yang gagal di dashboard
 * - Fondasi EDA: saat RabbitMQ aktif, kolom 'channel' berubah ke 'rabbitmq'
 *
 * APPEND-ONLY — tidak di-delete kecuali cleanup otomatis setelah N hari.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();

            // Nama event class, contoh: 'TagihanDibuat', 'MeteranDibaca', 'PaymentCallbackProcessed'
            $table->string('event_name', 150);

            // Domain entitas yang terkena event: 'Tagihan', 'Pelanggan', 'Transaksi'
            $table->string('aggregate_type', 100)->nullable();

            // ID entitas yang bersangkutan
            $table->unsignedBigInteger('aggregate_id')->nullable();

            // Data event yang relevan untuk debugging
            $table->json('payload')->nullable();

            // User yang men-trigger event (nullable — bisa dari sistem/scheduler)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Channel saat ini: 'internal' (Laravel Event System)
            // Nanti bisa: 'rabbitmq' saat integrasi RabbitMQ aktif
            $table->string('channel', 50)->default('internal');

            // Status pemrosesan event
            $table->enum('status', [
                'dispatched', // event sudah di-fire
                'consumed',   // listener sudah berhasil handle
                'failed',     // listener gagal (untuk alerting)
            ])->default('dispatched');

            // Pesan error jika status = 'failed'
            $table->text('error_message')->nullable();

            // Hanya created_at — append-only, tidak ada updated_at
            $table->timestamp('created_at')->nullable();

            // ── Index ─────────────────────────────────────────────────
            $table->index('event_name', 'idx_event_logs_name');

            // Composite untuk query "semua event untuk entitas X"
            $table->index(['aggregate_type', 'aggregate_id'], 'idx_event_logs_aggregate');

            $table->index('channel', 'idx_event_logs_channel');
            $table->index('status', 'idx_event_logs_status');
            $table->index('created_at', 'idx_event_logs_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_logs');
    }
};
