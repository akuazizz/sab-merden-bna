<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pelanggan\Repositories\PelangganRepository;
use App\Modules\Tagihan\Models\Tagihan;
use App\Modules\Tagihan\Repositories\TagihanRepository;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly PelangganRepository $pelangganRepo,
        private readonly TagihanRepository   $tagihanRepo,
    ) {}

    public function index(): View
    {
        $stats = [
            'total_pelanggan'    => $this->pelangganRepo->countByStatus('aktif'),
            'total_outstanding'  => $this->tagihanRepo->totalOutstanding(),
            'tagihan_bulan_ini'  => $this->tagihanRepo->countByPeriode(now()->format('Y-m')),
            'tagihan_jatuh_tempo'=> $this->tagihanRepo->getOutstanding()->filter(
                fn($t) => $t->status->value === 'jatuh_tempo'
            )->count(),
        ];

        $tagihanTerbaru = Tagihan::with('pelanggan:id,nama,nomor_pelanggan')
            ->whereNotIn('status', ['draft'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Data chart: jumlah tagihan terbit per bulan, 6 bulan terakhir
        $chartData = collect(range(5, 0))->map(function ($i) {
            $bulan = now()->subMonths($i);
            $count = Tagihan::where('periode', $bulan->format('Y-m'))
                ->whereNotIn('status', ['draft', 'void'])
                ->count();
            return [
                'label' => $bulan->translatedFormat('M'),
                'count' => $count,
            ];
        });

        $chartMax = max($chartData->max('count'), 1); // hindari division by zero

        return view('admin.dashboard', compact('stats', 'tagihanTerbaru', 'chartData', 'chartMax'));
    }
}

