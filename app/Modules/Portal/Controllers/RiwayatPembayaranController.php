<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatPembayaranController extends Controller
{
    public function index(Request $request): View
    {
        $pelanggan = auth()->user()->pelanggan;
        abort_unless($pelanggan, 403);

        $riwayat = Transaksi::query()
            ->where('pelanggan_id', $pelanggan->id)
            ->where('status', 'success')
            ->with(['tagihan:id,nomor_tagihan,periode,total_tagihan'])
            ->orderByDesc('paid_at')
            ->paginate(15)
            ->withQueryString();

        return view('portal.riwayat.index', compact('riwayat', 'pelanggan'));
    }

    /**
     * Detail transaksi sukses — bisa dipakai sebagai bukti bayar.
     */
    public function show(int $id): View
    {
        $pelanggan = auth()->user()->pelanggan;
        abort_unless($pelanggan, 403);

        $transaksi = Transaksi::where('pelanggan_id', $pelanggan->id)
            ->where('status', 'success')
            ->with(['tagihan', 'pelanggan'])
            ->findOrFail($id);

        return view('portal.riwayat.show', compact('transaksi'));
    }
}
