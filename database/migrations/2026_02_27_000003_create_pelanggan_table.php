<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pelanggan — data lengkap pelanggan air bersih.
 *
 * Relasi ke users bersifat opsional (1:1 nullable).
 * Pelanggan bisa ada tanpa akun login (didaftarkan admin).
 * Menggunakan soft delete agar riwayat tagihan tetap terjaga.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();

            // FK ke users — nullable karena pelanggan bisa tanpa akun
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Format: SAB-YYYYNNN, contoh: SAB-2024001
            $table->string('nomor_pelanggan', 20)->unique();

            $table->string('nama', 100);

            // NIK 16 digit — nullable karena mungkin tidak selalu diisi
            $table->string('nik', 16)->nullable()->unique();

            $table->text('alamat');
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('dusun', 50)->nullable();
            $table->string('telepon', 20)->nullable();

            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');

            $table->date('tanggal_daftar');

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            // ── Index ─────────────────────────────────────────────────
            $table->index('nomor_pelanggan', 'idx_pelanggan_nomor');
            $table->index('status', 'idx_pelanggan_status');
            $table->index('user_id', 'idx_pelanggan_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
