<?php

namespace App\Modules\Payment\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTransaksiController extends Controller
{
    public function index(Request $request): View
    {
        $status  = $request->string('status')->toString() ?: null;
        $periode = $request->string('periode', '')->toString();
        $search  = $request->string('search')->toString() ?: null;

        $transaksi = Transaksi::query()
            ->with(['tagihan.pelanggan:id,nomor_pelanggan,nama'])
            ->when($status,  fn($q) => $q->where('status', $status))
            ->when($search,  fn($q) => $q->where('kode_transaksi', 'like', "%{$search}%"))
            ->when($periode, fn($q) => $q->whereHas('tagihan', fn($tq) => $tq->where('periode', $periode)))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        // Stats ringkasan
        $stats = [
            'total_success'  => Transaksi::where('status', 'success')->count(),
            'total_pending'  => Transaksi::where('status', 'pending')->count(),
            'total_failed'   => Transaksi::whereIn('status', ['failed','cancelled','expired'])->count(),
            'total_revenue'  => Transaksi::where('status', 'success')->sum('jumlah'),
        ];

        $statusOptions = ['pending', 'success', 'failed', 'cancelled', 'expired'];

        return view('admin.pembayaran.index', compact('transaksi', 'stats', 'status', 'periode', 'search', 'statusOptions'));
    }

    public function show(int $id): View
    {
        $transaksi = Transaksi::with([
            'tagihan.pelanggan:id,nomor_pelanggan,nama,telepon,alamat',
            'tagihan',
            'paymentLogs',
        ])->findOrFail($id);

        return view('admin.pembayaran.show', compact('transaksi'));
    }
}
