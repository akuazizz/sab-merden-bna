<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel tagihan — tagihan bulanan yang di-generate dari meter_readings.
 *
 * Relasi meter_reading_id UNIQUE: 1 meter reading → 1 tagihan.
 *
 * harga_per_kubik dan biaya_admin adalah SNAPSHOT saat generate —
 * tidak terpengaruh perubahan pengaturan di masa mendatang.
 *
 * State machine status:
 *   draft → terbit → sebagian → lunas
 *                 ↓
 *           jatuh_tempo → lunas
 *   (semua bisa → void kecuali lunas)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pelanggan_id')
                  ->constrained('pelanggan');

            // UNIQUE: satu meter reading hanya boleh menghasilkan satu tagihan
            $table->foreignId('meter_reading_id')
                  ->unique()
                  ->constrained('meter_readings');

            // Format: INV-202401-001
            $table->string('nomor_tagihan', 30)->unique();

            // Format 'YYYY-MM'
            $table->char('periode', 7);

            // ── Komponen Tagihan ──────────────────────────────────────
            $table->decimal('pemakaian_kubik', 10, 2);

            // Snapshot harga saat generate (bukan harga saat bayar)
            $table->decimal('harga_per_kubik', 10, 2);
            $table->decimal('biaya_admin', 10, 2)->default(0);

            // Denda terlambat — default 0, dihitung scheduler saat jatuh tempo
            $table->decimal('denda', 10, 2)->default(0);

            // total = (pemakaian × harga) + biaya_admin + denda
            $table->decimal('total_tagihan', 12, 2);

            // ── State Machine ─────────────────────────────────────────
            $table->enum('status', [
                'draft',        // baru dibuat, belum diterbitkan
                'terbit',       // sudah bisa dibayar
                'sebagian',     // bayar sebagian (untuk kasus multi-payment)
                'lunas',        // TERMINAL STATE — telah dibayar penuh
                'jatuh_tempo',  // melewati tanggal jatuh tempo
                'void',         // TERMINAL STATE — dibatalkan admin
            ])->default('draft');

            // ── Tanggal ───────────────────────────────────────────────
            $table->date('tanggal_terbit')->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable();

            // Diisi oleh system saat status berubah ke 'lunas'
            $table->timestamp('tanggal_lunas')->nullable();

            $table->text('catatan')->nullable();
            $table->timestamps();

            // ── Index ─────────────────────────────────────────────────
            $table->index('pelanggan_id', 'idx_tagihan_pelanggan');
            $table->index('periode', 'idx_tagihan_periode');
            $table->index('status', 'idx_tagihan_status');
            $table->index('nomor_tagihan', 'idx_tagihan_nomor');
            $table->index('tanggal_jatuh_tempo', 'idx_tagihan_jatuh_tempo');

            // Covering index untuk laporan keuangan
            $table->index(
                ['status', 'tanggal_terbit', 'periode', 'total_tagihan', 'pelanggan_id'],
                'idx_tagihan_laporan'
            );

            // Covering index untuk query per pelanggan per tahun
            $table->index(
                ['pelanggan_id', 'periode', 'status', 'total_tagihan'],
                'idx_tagihan_pelanggan_tahun'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
