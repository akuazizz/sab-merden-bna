<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename kolom 'nik' → 'id_pelanggan' di tabel pelanggan.
 * Unique constraint juga di-rename agar rollback tetap konsisten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            // Hapus unique index lama sebelum rename
            $table->dropUnique(['nik']);

            // Rename kolom
            $table->renameColumn('nik', 'id_pelanggan');

            // Buat ulang unique index dengan nama baru
            $table->unique('id_pelanggan', 'pelanggan_id_pelanggan_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropUnique('pelanggan_id_pelanggan_unique');
            $table->renameColumn('id_pelanggan', 'nik');
            $table->unique('nik');
        });
    }
};
