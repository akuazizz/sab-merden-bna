<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tagihan\Models\Tagihan;
use App\Modules\Tagihan\Services\TagihanService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalTagihanController extends Controller
{
    public function __construct(
        private readonly TagihanService $service,
    ) {}

    /**
     * Daftar tagihan milik pelanggan yang login.
     * Guard: status bukan draft, milik pelanggan ini saja.
     */
    public function index(Request $request): View
    {
        $pelanggan = auth()->user()->pelanggan;
        abort_unless($pelanggan, 403);

        $status  = $request->string('status')->toString() ?: null;
        $tagihan = Tagihan::query()
            ->where('pelanggan_id', $pelanggan->id)
            ->whereNotIn('status', ['draft'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['meterReading:id,periode,pemakaian'])
            ->orderByDesc('periode')
            ->paginate(10)
            ->withQueryString();

        return view('portal.tagihan.index', compact('tagihan', 'pelanggan', 'status'));
    }

    /**
     * Detail tagihan + history transaksi pelanggan.
     * Guard: tagihan harus milik pelanggan yang login.
     */
    public function show(int $id): View
    {
        $pelanggan = auth()->user()->pelanggan;
        abort_unless($pelanggan, 403);

        $tagihan = Tagihan::where('pelanggan_id', $pelanggan->id)
            ->with([
                'meterReading',
                'transaksi' => fn($q) => $q->latest()->limit(5),
            ])
            ->findOrFail($id);

        return view('portal.tagihan.show', compact('tagihan', 'pelanggan'));
    }
}
