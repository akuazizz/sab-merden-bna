<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Exports\LaporanBulananExport;
use App\Modules\Payment\Models\Transaksi;
use App\Modules\Tagihan\Models\Tagihan;
use App\Modules\Pelanggan\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLaporanController extends Controller
{
    public function index(Request $request): View
    {
        $tahun = $request->integer('tahun', now()->year);

        // ── Pendapatan per bulan (12 bulan dalam setahun) ────────────
        $pendapatanBulanan = Transaksi::selectRaw(
                'MONTH(paid_at) as bulan, SUM(jumlah) as total, COUNT(*) as jumlah_transaksi'
            )
            ->where('status', 'success')
            ->whereYear('paid_at', $tahun)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        // Lengkapi 12 bulan (bulan yang belum ada = 0)
        $grafikPendapatan = collect(range(1, 12))->map(fn($m) => [
            'bulan'             => $m,
            'label'             => \Carbon\Carbon::create($tahun, $m, 1)->translatedFormat('M'),
            'total'             => (float) ($pendapatanBulanan[$m]->total ?? 0),
            'jumlah_transaksi'  => (int)   ($pendapatanBulanan[$m]->jumlah_transaksi ?? 0),
        ]);

        // ── Ringkasan keseluruhan ─────────────────────────────────────
        $summary = [
            'total_pendapatan_tahun' => Transaksi::where('status', 'success')
                ->whereYear('paid_at', $tahun)->sum('jumlah'),
            'total_tagihan_lunas'    => Tagihan::where('status', 'lunas')
                ->whereYear('tanggal_lunas', $tahun)->count(),
            'total_tunggakan'        => Tagihan::whereIn('status', ['terbit', 'jatuh_tempo'])->sum('total_tagihan'),
            'total_pelanggan_aktif'  => Pelanggan::where('status', 'aktif')->count(),
            'tagihan_jatuh_tempo'    => Tagihan::where('status', 'jatuh_tempo')->count(),
            'tagihan_belum_bayar'    => Tagihan::where('status', 'terbit')->count(),
        ];

        // ── Top pelanggan tunggakan terbesar ──────────────────────────
        $topTunggakan = Tagihan::with('pelanggan:id,nomor_pelanggan,nama')
            ->whereIn('status', ['terbit', 'jatuh_tempo'])
            ->select('pelanggan_id', DB::raw('SUM(total_tagihan) as total_tunggakan'), DB::raw('COUNT(*) as jumlah_tagihan'))
            ->groupBy('pelanggan_id')
            ->orderByDesc('total_tunggakan')
            ->limit(5)
            ->get();

        // ── Data per periode (bulan) ──────────────────────────────────
        $laporanPeriode = Tagihan::selectRaw(
                'periode,
                COUNT(*) as total_tagihan,
                SUM(CASE WHEN status = "lunas" THEN 1 ELSE 0 END) as lunas,
                SUM(CASE WHEN status IN ("terbit","jatuh_tempo") THEN 1 ELSE 0 END) as belum_bayar,
                SUM(CASE WHEN status = "void" THEN 1 ELSE 0 END) as void,
                SUM(CASE WHEN status = "lunas" THEN total_tagihan ELSE 0 END) as pendapatan,
                SUM(CASE WHEN status IN ("terbit","jatuh_tempo") THEN total_tagihan ELSE 0 END) as tunggakan'
            )
            ->whereYear('tanggal_terbit', $tahun)
            ->whereNotIn('status', ['draft'])
            ->groupBy('periode')
            ->orderByDesc('periode')
            ->get();

        $tahunOptions = range(now()->year, 2024);

        return view('admin.laporan.index', compact(
            'grafikPendapatan', 'summary', 'topTunggakan', 'laporanPeriode', 'tahun', 'tahunOptions'
        ));
    }

    /**
     * GET /admin/laporan/download?tahun=2026&bulan=3
     * Download laporan bulanan sebagai file Excel (.xlsx)
     */
    public function download(Request $request): StreamedResponse
    {
        $request->validate([
            'tahun' => ['required', 'numeric', 'min:2020', 'max:' . (now()->year + 1)],
            'bulan' => ['required', 'numeric', 'min:1', 'max:12'],
        ]);

        $export = new LaporanBulananExport(
            tahun: $request->integer('tahun'),
            bulan: $request->integer('bulan'),
        );

        return $export->download();
    }
}
