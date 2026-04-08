<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pengaturan — konfigurasi sistem yang bisa diubah admin
 * tanpa perlu deploy ulang (harga/kubik, biaya admin, denda, dll).
 *
 * Dibuat sebelum pelanggan karena tidak ada FK dependency.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->autoIncrement();

            // Kunci unik, contoh: 'harga_per_kubik', 'biaya_admin'
            $table->string('kunci', 100)->unique();

            // Nilai disimpan sebagai string — cast di aplikasi sesuai tipe
            $table->text('nilai');

            $table->string('deskripsi', 255)->nullable();

            // Tidak perlu created_at, hanya updated_at
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
