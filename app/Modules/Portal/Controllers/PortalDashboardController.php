<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Meteran\Repositories\MeteranRepository;
use App\Modules\Pelanggan\Repositories\PelangganRepository;
use App\Modules\Tagihan\Repositories\TagihanRepository;
use Illuminate\View\View;

class PortalDashboardController extends Controller
{
    public function __construct(
        private readonly TagihanRepository   $tagihanRepo,
        private readonly MeteranRepository   $meteranRepo,
    ) {}

    public function index(): View
    {
        $pelanggan = auth()->user()->pelanggan;

        abort_unless($pelanggan, 403, 'Akun Anda belum terhubung ke data pelanggan.');

        $stats = [
            'total_outstanding'  => $this->tagihanRepo->totalOutstandingByPelanggan($pelanggan->id),
            'tagihan_aktif'      => $this->tagihanRepo->getOutstanding()->where('pelanggan_id', $pelanggan->id)->count(),
            'total_pemakaian'    => $this->meteranRepo->totalPemakaianPerTahun($pelanggan->id, now()->year),
        ];

        // Data 6 bulan terakhir untuk chart pemakaian
        $grafikPemakaian = $this->meteranRepo->getPemakaian6BulanTerakhir($pelanggan->id, 6)
            ->map(fn($r) => [
                'label'     => \Carbon\Carbon::createFromFormat('Y-m', $r->periode)->translatedFormat('M Y'),
                'pemakaian' => (float) $r->pemakaian,
            ]);

        // Tagihan terbaru (5)
        $tagihanTerbaru = $this->tagihanRepo->getByPelanggan($pelanggan->id, perPage: 5);

        return view('portal.dashboard', compact('pelanggan', 'stats', 'grafikPemakaian', 'tagihanTerbaru'));
    }
}
