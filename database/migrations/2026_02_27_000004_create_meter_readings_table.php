<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel meter_readings — pembacaan meteran bulanan per pelanggan.
 *
 * Unique constraint (pelanggan_id, periode) memastikan
 * hanya ada 1 pembacaan per pelanggan per bulan.
 *
 * Kolom pemakaian dihitung di service layer, bukan trigger DB,
 * agar logika bisnis tetap di aplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pelanggan_id')
                  ->constrained('pelanggan')
                  ->cascadeOnDelete();

            // Format 'YYYY-MM', contoh: '2024-01'
            // CHAR(7) lebih efisien dari VARCHAR untuk fixed-length string
            $table->char('periode', 7);

            // Angka meter dalam m³ — presisi 2 desimal
            $table->decimal('kubik_awal', 10, 2)->default(0);
            $table->decimal('kubik_akhir', 10, 2);

            // Dihitung di service: kubik_akhir - kubik_awal
            $table->decimal('pemakaian', 10, 2);

            // Petugas yang mencatat — nullable karena user bisa dihapus
            $table->foreignId('dicatat_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Opsional: path foto bukti meteran
            $table->string('foto_meteran', 255)->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            // ── Constraints ───────────────────────────────────────────
            // KRITIS: mencegah duplikasi pembacaan per bulan
            $table->unique(['pelanggan_id', 'periode'], 'uq_meter_pelanggan_periode');

            // ── Index ─────────────────────────────────────────────────
            $table->index('pelanggan_id', 'idx_meter_pelanggan');
            $table->index('periode', 'idx_meter_periode');
            $table->index('dicatat_oleh', 'idx_meter_petugas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
