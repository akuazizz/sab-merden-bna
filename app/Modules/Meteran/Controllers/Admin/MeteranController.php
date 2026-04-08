<?php

namespace App\Modules\Meteran\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Meteran\Exceptions\KubikTidakValidException;
use App\Modules\Meteran\Exceptions\MeteranSudahDiinputException;
use App\Modules\Meteran\Models\MeteranReading;
use App\Modules\Meteran\Requests\StoreMeteranRequest;
use App\Modules\Meteran\Requests\UpdateMeteranRequest;
use App\Modules\Meteran\Services\MeteranService;
use App\Modules\Pelanggan\Exceptions\PelangganNonaktifException;
use App\Modules\Pelanggan\Repositories\PelangganRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Admin MeteranController — input dan koreksi pembacaan meteran.
 *
 * Controller tidak menyentuh business logic — semua delegasi ke MeteranService.
 * Foto meteran disimpan via Storage::disk('public') agar bisa diakses via URL.
 */
class MeteranController extends Controller
{
    public function __construct(
        private readonly MeteranService      $service,
        private readonly PelangganRepository $pelangganRepo,
    ) {}

    // ── index ────────────────────────────────────────────────────────

    /**
     * Daftar meter readings dengan filter periode dan pelanggan.
     */
    public function index(Request $request): View
    {
        $periode    = $request->string('periode', '')->toString();
        $pelangganId = $request->integer('pelanggan_id') ?: null;

        $readings = MeteranReading::query()
            ->with(['pelanggan:id,nomor_pelanggan,nama', 'tagihan:id,meter_reading_id,status,nomor_tagihan'])
            ->when($periode, fn($q) => $q->where('periode', $periode))
            ->when($pelangganId, fn($q) => $q->where('pelanggan_id', $pelangganId))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();  // pertahankan filter saat navigasi halaman

        // Data untuk dropdown filter pelanggan di view
        $pelangganList = $this->pelangganRepo->allAktif();

        // Daftar pelanggan yang belum input bulan ini (untuk badge reminder)
        $belumInput = $this->service->getBelumInput();

        return view('admin.meteran.index', compact(
            'readings', 'periode', 'pelangganList', 'belumInput'
        ));
    }

    // ── create / store ───────────────────────────────────────────────

    /**
     * Form input meteran baru.
     * Bisa menerima ?pelanggan_id=X dan ?periode=YYYY-MM dari query string
     * untuk pre-fill form (dari halaman pelanggan atau reminder).
     */
    public function create(Request $request): View
    {
        $pelangganList = $this->pelangganRepo->allAktif();
        $pelangganId   = $request->integer('pelanggan_id') ?: null;
        $periode       = $request->string('periode', now()->format('Y-m'))->toString();

        // Suggestion kubik_awal dari bulan lalu (jika pelanggan sudah dipilih)
        $kubikAwalSuggestion = $pelangganId
            ? $this->service->getKubikAwalSuggestion($pelangganId, $periode)
            : null;

        return view('admin.meteran.create', compact(
            'pelangganList', 'pelangganId', 'periode', 'kubikAwalSuggestion'
        ));
    }

    public function store(StoreMeteranRequest $request): RedirectResponse
    {
        try {
            // Handle upload foto sebelum memanggil service
            $data = $request->validated();
            if ($request->hasFile('foto_meteran')) {
                $data['foto_meteran'] = $request->file('foto_meteran')
                    ->store('meteran/' . $data['periode'], 'public');
            }

            $reading = $this->service->catat($data, dicatatOleh: $request->user()->id);

            return redirect()
                ->route('admin.meteran.show', $reading->id)
                ->with('success', "Meter reading untuk {$reading->pelanggan->nama} "
                    . "periode {$reading->periode} berhasil disimpan. "
                    . "Pemakaian: {$reading->pemakaian} m³.");

        } catch (PelangganNonaktifException $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());

        } catch (MeteranSudahDiinputException $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());

        } catch (KubikTidakValidException $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', 'Gagal menyimpan meter reading: ' . $e->getMessage());
        }
    }

    // ── show ─────────────────────────────────────────────────────────

    public function show(int $id): View
    {
        $reading = MeteranReading::findOrFail($id);
        $reading->load([
            'pelanggan:id,nomor_pelanggan,nama,alamat',
            'petugas:id,name',
            'tagihan',
        ]);

        // Data grafik pemakaian 6 bulan terakhir untuk chart di view
        $grafikPemakaian = $this->service->getPemakaianGrafik(
            $reading->pelanggan_id,
            nBulan: 6
        );

        return view('admin.meteran.show', compact('reading', 'grafikPemakaian'));
    }

    // ── edit / update ────────────────────────────────────────────────

    /**
     * Form koreksi meter reading.
     * Guard di service: gagal jika tagihan sudah berstatus bukan draft.
     */
    public function edit(int $id): View
    {
        $reading = MeteranReading::with(['pelanggan:id,nomor_pelanggan,nama', 'tagihan:id,meter_reading_id,status'])
            ->findOrFail($id);

        // Peringatan jika tagihan sudah melewati draft (view bisa disable form)
        $tagihanSudahTerbit = $reading->tagihan
            && !in_array($reading->tagihan->status->value, ['draft']);

        return view('admin.meteran.edit', compact('reading', 'tagihanSudahTerbit'));
    }

    public function update(UpdateMeteranRequest $request, int $id): RedirectResponse
    {
        try {
            $data = $request->validated();

            // Handle upload foto baru (opsional)
            if ($request->hasFile('foto_meteran')) {
                // Hapus foto lama jika ada
                $reading = MeteranReading::findOrFail($id);
                if ($reading->foto_meteran) {
                    Storage::disk('public')->delete($reading->foto_meteran);
                }
                $data['foto_meteran'] = $request->file('foto_meteran')
                    ->store('meteran/' . $reading->periode, 'public');
            }

            $reading = $this->service->koreksi($id, $data);

            return redirect()
                ->route('admin.meteran.show', $reading->id)
                ->with('success', "Koreksi meter reading berhasil. "
                    . "Pemakaian diperbarui: {$reading->pemakaian} m³.");

        } catch (KubikTidakValidException $e) {
            return back()->withInput()->with('error', $e->getMessage());

        } catch (\RuntimeException $e) {
            // Termasuk guard "tagihan sudah terbit"
            return back()->withInput()->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui meter reading: ' . $e->getMessage());
        }
    }

    // ── API: kubik_awal suggestion ───────────────────────────────────

    /**
     * Endpoint AJAX — ambil kubik_awal suggestion dari bulan lalu.
     * Dipanggil saat form create memilih pelanggan + periode.
     *
     * GET /admin/meteran/suggestion?pelanggan_id=1&periode=2024-02
     * Response: { "kubik_awal": 125.50 } atau { "kubik_awal": null }
     */
    public function suggestion(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'pelanggan_id' => ['required', 'integer'],
            'periode'      => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        $suggestion = $this->service->getKubikAwalSuggestion(
            $request->integer('pelanggan_id'),
            $request->string('periode')->toString(),
        );

        return response()->json(['kubik_awal' => $suggestion]);
    }
}
