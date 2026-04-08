<?php

namespace Database\Seeders;

use App\Modules\Meteran\Models\MeteranReading;
use App\Modules\Pelanggan\Models\Pelanggan;
use App\Modules\Tagihan\Models\Tagihan;
use Illuminate\Database\Seeder;

class TagihanTestSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = Pelanggan::first();

        if (!$pelanggan) {
            $this->command->warn('Tidak ada pelanggan. Pastikan ada data pelanggan dulu.');
            return;
        }

        $this->command->info("Pelanggan: {$pelanggan->nama} (ID: {$pelanggan->id})");

        // ── 1. Buat Meter Reading (FK tagihan → meter_readings) ──────────
        $reading = MeteranReading::firstOrCreate(
            [
                'pelanggan_id' => $pelanggan->id,
                'periode'      => '2026-02',
            ],
            [
                'kubik_awal'   => 100.00,
                'kubik_akhir'  => 115.00,
                'pemakaian'    => 15.00,
                'dicatat_oleh' => 1,   // admin user id
                'foto_meteran' => null,
                'catatan'      => 'Data test untuk payment demo',
            ]
        );
        $this->command->info("✓ Meter Reading ID: {$reading->id} | Periode: {$reading->periode}");

        // ── 2. Buat Tagihan ter-link ke meter reading ────────────────────
        $tagihan = Tagihan::firstOrCreate(
            ['meter_reading_id' => $reading->id],
            [
                'pelanggan_id'        => $pelanggan->id,
                'nomor_tagihan'       => 'SAB-2026-02-' . str_pad($pelanggan->id, 4, '0', STR_PAD_LEFT),
                'periode'             => '2026-02',
                'pemakaian_kubik'     => 15.00,
                'harga_per_kubik'     => 2500.00,
                'biaya_admin'         => 5000.00,
                'denda'               => 0.00,
                'total_tagihan'       => 42500.00,   // (15 × 2500) + 5000
                'status'              => 'terbit',
                'tanggal_terbit'      => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
                'catatan'             => null,
            ]
        );

        $this->command->info(
            "✓ Tagihan ID: {$tagihan->id} | {$tagihan->nomor_tagihan} | " .
            "Rp " . number_format($tagihan->total_tagihan, 0, ',', '.') .
            " | Status: {$tagihan->status->value}"
        );
        $this->command->newLine();
        $this->command->info('🎯 Data siap untuk testing payment Midtrans!');
        $this->command->info("   Login sebagai: pelanggan@sab.test / password");
        $this->command->info("   Lalu buka: http://127.0.0.1:8000/portal/tagihan/{$tagihan->id}");
    }
}
