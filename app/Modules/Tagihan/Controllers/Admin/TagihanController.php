<?php

namespace App\Modules\Tagihan\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Tagihan\Enums\TagihanStatus;
use App\Modules\Tagihan\Exceptions\TransisiStatusTidakValidException;
use App\Modules\Tagihan\Models\Tagihan;
use App\Modules\Tagihan\Repositories\TagihanRepository;
use App\Modules\Tagihan\Requests\VoidTagihanRequest;
use App\Modules\Tagihan\Services\TagihanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagihanController extends Controller
{
    public function __construct(
        private readonly TagihanService    $service,
        private readonly TagihanRepository $repo,
    ) {}

    // ── index ────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $status  = $request->string('status')->toString() ?: null;
        $periode = $request->string('periode', '')->toString();

        $tagihan = Tagihan::query()
            ->with(['pelanggan:id,nomor_pelanggan,nama'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($periode, fn($q) => $q->where('periode', $periode))
            ->whereNotIn('status', ['draft'])
            ->orderByDesc('tanggal_terbit')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_outstanding' => $this->repo->totalOutstanding(),
            'count_overdue'     => Tagihan::where('status', TagihanStatus::JatuhTempo)->count(),
            'count_bulan_ini'   => $periode ? $this->repo->countByPeriode($periode) : Tagihan::whereNotIn('status', ['draft','void'])->count(),
        ];

        $statusOptions = TagihanStatus::cases();

        return view('admin.tagihan.index', compact('tagihan', 'stats', 'status', 'periode', 'statusOptions'));
    }

    // ── show ─────────────────────────────────────────────────────────

    public function show(int $id): View
    {
        $tagihan = Tagihan::with([
            'pelanggan:id,nomor_pelanggan,nama,telepon,alamat',
            'meterReading',
            'transaksi' => fn($q) => $q->latest()->with('paymentLogs'),
        ])->findOrFail($id);

        return view('admin.tagihan.show', compact('tagihan'));
    }

    // ── void ─────────────────────────────────────────────────────────

    /**
     * PATCH /admin/tagihan/{id}/void
     * Batalkan tagihan — hanya bisa jika belum lunas.
     */
    public function void(VoidTagihanRequest $request, int $id): RedirectResponse
    {
        $tagihan = Tagihan::findOrFail($id);

        try {
            $this->service->void($tagihan, $request->validated('alasan'));

            return redirect()
                ->route('admin.tagihan.show', $id)
                ->with('success', "Tagihan {$tagihan->nomor_tagihan} berhasil dibatalkan.");

        } catch (TransisiStatusTidakValidException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan tagihan: ' . $e->getMessage());
        }
    }

    // ── tandaiLunas ──────────────────────────────────────────────────

    /**
     * PATCH /admin/tagihan/{id}/lunas
     * Tandai lunas secara manual (pembayaran tunai/offline).
     */
    public function tandaiLunas(int $id): RedirectResponse
    {
        $tagihan = Tagihan::findOrFail($id);

        try {
            $this->service->tandaiLunas($tagihan);

            return redirect()
                ->route('admin.tagihan.show', $id)
                ->with('success', "Tagihan {$tagihan->nomor_tagihan} ditandai lunas.");

        } catch (TransisiStatusTidakValidException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menandai lunas: ' . $e->getMessage());
        }
    }

    // ── markOverdue ──────────────────────────────────────────────────

    /**
     * POST /admin/tagihan/mark-overdue
     * Trigger manual bulk update jatuh tempo (biasanya dijalankan scheduler).
     */
    public function markOverdue(): RedirectResponse
    {
        try {
            $updated = $this->service->markOverdue();

            return back()->with(
                'success',
                $updated > 0
                    ? "{$updated} tagihan berhasil ditandai jatuh tempo."
                    : 'Tidak ada tagihan yang perlu ditandai jatuh tempo.'
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }
}
