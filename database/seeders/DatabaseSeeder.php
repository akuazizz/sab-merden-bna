<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Buat roles ──────────────────────────────────────────────
        $roleAdmin     = Role::firstOrCreate(['name' => 'admin',     'guard_name' => 'web']);
        $rolePelanggan = Role::firstOrCreate(['name' => 'pelanggan', 'guard_name' => 'web']);

        // ── 2. Buat akun Admin ─────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@sab.test'],
            [
                'name'      => 'Admin',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole($roleAdmin);

        // ── 3. Buat akun Pelanggan demo ────────────────────────────────
        $pelangganUser = User::firstOrCreate(
            ['email' => 'pelanggan@sab.test'],
            [
                'name'      => 'Test User',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $pelangganUser->assignRole($rolePelanggan);

        // ── 4. Buat data pelanggan demo ────────────────────────────────
        $pelanggan = \App\Modules\Pelanggan\Models\Pelanggan::firstOrCreate(
            ['nomor_pelanggan' => 'SAB-2026001'],
            [
                'user_id'        => $pelangganUser->id,
                'nama'           => 'Test User',
                'nik'            => null,
                'alamat'         => 'Jl. Merden No. 1',
                'dusun'          => 'Krajan',
                'rt'             => '001',
                'rw'             => '001',
                'telepon'        => '08100000001',
                'tanggal_daftar' => '2026-01-01',
                'status'         => 'aktif',
            ]
        );

        // ── 5. Pengaturan tarif awal ───────────────────────────────────
        \Illuminate\Support\Facades\DB::table('pengaturan')->insertOrIgnore([
            ['kunci' => 'harga_per_kubik',  'nilai' => '2500',   'deskripsi' => 'Harga per m³ (Rp)',        'updated_at' => now()],
            ['kunci' => 'biaya_admin',      'nilai' => '2000',   'deskripsi' => 'Biaya administrasi (Rp)',  'updated_at' => now()],
            ['kunci' => 'batas_jatuh_tempo','nilai' => '20',     'deskripsi' => 'Batas hari jatuh tempo',   'updated_at' => now()],
            ['kunci' => 'nama_desa',        'nilai' => 'Merden', 'deskripsi' => 'Nama desa',                'updated_at' => now()],
        ]);

        $this->command->info('✅ Seeding selesai!');
        $this->command->line('   Admin    : admin@sab.test / password');
        $this->command->line('   Pelanggan: pelanggan@sab.test / password');
    }
}
