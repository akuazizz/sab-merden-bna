<?php

namespace App\Modules\Payment\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Tagihan\Models\Tagihan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * PaymentController — endpoint untuk portal pelanggan initiate payment.
 * Dipanggil dari portal.tagihan.show saat klik "Bayar Sekarang".
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $service,
    ) {}

    /**
     * POST /portal/tagihan/{id}/bayar
     * Initiate Midtrans Snap payment.
     *
     * Response: JSON dengan snap_token agar frontend bisa panggil snap.pay()
     */
    public function initiate(Request $request, int $tagihanId): JsonResponse|RedirectResponse
    {
        $pelanggan = $request->user()->pelanggan;
        abort_unless($pelanggan, 403);

        $tagihan = Tagihan::where('pelanggan_id', $pelanggan->id)
            ->findOrFail($tagihanId);

        try {
            $transaksi = $this->service->initiate($tagihan, $pelanggan->id);

            // Jika request AJAX → kembalikan snap_token untuk Snap.js
            if ($request->expectsJson()) {
                return response()->json([
                    'snap_token'   => $transaksi->snap_token,
                    'redirect_url' => $transaksi->snap_redirect_url,
                ]);
            }

            // Jika request biasa → redirect ke snap redirect URL
            return redirect()->away($transaksi->snap_redirect_url);

        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * GET /portal/tagihan/{id}/bayar/status
     * Cek status transaksi terbaru untuk polling dari frontend.
     */
    public function status(int $tagihanId): JsonResponse
    {
        $pelanggan = auth()->user()->pelanggan;
        abort_unless($pelanggan, 403);

        $tagihan = Tagihan::where('pelanggan_id', $pelanggan->id)->findOrFail($tagihanId);

        $transaksi = $tagihan->transaksiPendingAktif()->first()
            ?? $tagihan->transaksiSukses()->first();

        return response()->json([
            'tagihan_status'   => $tagihan->fresh()->status->value,
            'transaksi_status' => $transaksi?->status->value,
        ]);
    }
}
