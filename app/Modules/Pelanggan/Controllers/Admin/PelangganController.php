<?php

namespace App\Modules\Pelanggan\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Pelanggan\Exceptions\PelangganMasihMemilikiTagihanException;
use App\Modules\Pelanggan\Exceptions\PelangganNotFoundException;
use App\Modules\Pelanggan\Exceptions\PelangganNonaktifException;
use App\Modules\Pelanggan\Requests\StorePelangganRequest;
use App\Modules\Pelanggan\Requests\UpdatePelangganRequest;
use App\Modules\Pelanggan\Services\PelangganService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin PelangganController — CRUD + deactivate/activate.
 *
 * Strict separation of concerns:
 *   - Controller hanya: validate input, call service, handle response
 *   - Business logic SELURUHNYA ada di PelangganService
 *   - Exception handling via try-catch → flash message
 */
class PelangganController extends Controller
{
    public function __construct(
        private readonly PelangganService $service,
    ) {}

    // ── index ────────────────────────────────────────────────────────

    /**
     * Daftar pelanggan dengan search dan paginasi.
     */
    public function index(Request $request): View
    {
        $keyword    = $request->string('q')->toString();
        $pelanggan  = $keyword
            ? $this->service->search($keyword, perPage: 15)
            : $this->service->paginate(perPage: 15);

        return view('admin.pelanggan.index', [
            'pelanggan' => $pelanggan,
            'keyword'   => $keyword,
        ]);
    }

    // ── create / store ───────────────────────────────────────────────

    public function create(): View
    {
        // User yang belum terhubung ke pelanggan manapun (role pelanggan / belum punya pelanggan)
        $availableUsers = \App\Models\User::whereDoesntHave('pelanggan')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.pelanggan.create', compact('availableUsers'));
    }

    public function store(StorePelangganRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $pelanggan = $this->service->create($validated);

            return redirect()
                ->route('admin.pelanggan.show', $pelanggan->id)
                ->with('success', "Pelanggan {$pelanggan->nama} berhasil didaftarkan. Nomor: {$pelanggan->nomor_pelanggan}")
                ->with('akun_baru', [
                    'email'    => $validated['email'],
                    'password' => $validated['password'],
                ]);

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mendaftarkan pelanggan: ' . $e->getMessage());
        }
    }

    // ── show ─────────────────────────────────────────────────────────

    public function show(int $id): View
    {
        try {
            $pelanggan = $this->service->findOrFail($id);

            $pelanggan->loadMissing([
                'tagihanAktif',
                'meterReadings' => fn($q) => $q->orderByDesc('periode')->limit(6),
            ]);

            return view('admin.pelanggan.show', compact('pelanggan'));

        } catch (PelangganNotFoundException $e) {
            abort(404, $e->getMessage());
        }
    }

    // ── edit / update ────────────────────────────────────────────────

    public function edit(int $id): View
    {
        try {
            $pelanggan = $this->service->findOrFail($id);

            // Available users = belum punya pelanggan ATAU user yang sudah terhubung ke pelanggan ini
            $availableUsers = \App\Models\User::where(function ($q) use ($pelanggan) {
                $q->whereDoesntHave('pelanggan')
                  ->orWhereHas('pelanggan', fn($p) => $p->where('id', $pelanggan->id));
            })->orderBy('name')->get(['id', 'name', 'email']);

            return view('admin.pelanggan.edit', compact('pelanggan', 'availableUsers'));

        } catch (PelangganNotFoundException $e) {
            abort(404, $e->getMessage());
        }
    }

    public function update(UpdatePelangganRequest $request, int $id): RedirectResponse
    {
        try {
            $pelanggan = $this->service->update($id, $request->validated());

            return redirect()
                ->route('admin.pelanggan.show', $pelanggan->id)
                ->with('success', "Data pelanggan {$pelanggan->nama} berhasil diperbarui.");

        } catch (PelangganNotFoundException $e) {
            return redirect()
                ->route('admin.pelanggan.index')
                ->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    // ── destroy ──────────────────────────────────────────────────────

    /**
     * Soft delete pelanggan.
     * Guard: ditolak jika masih punya tagihan aktif.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $pelanggan = $this->service->findOrFail($id);
            $nama      = $pelanggan->nama;

            $this->service->delete($id);

            return redirect()
                ->route('admin.pelanggan.index')
                ->with('success', "Pelanggan {$nama} berhasil dihapus.");

        } catch (PelangganMasihMemilikiTagihanException $e) {
            return back()->with('error', $e->getMessage());

        } catch (PelangganNotFoundException $e) {
            return redirect()
                ->route('admin.pelanggan.index')
                ->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus pelanggan: ' . $e->getMessage());
        }
    }

    // ── deactivate / activate ────────────────────────────────────────

    /**
     * Nonaktifkan pelanggan.
     * Endpoint: PATCH /admin/pelanggan/{id}/deactivate
     *
     * Guard: ditolak jika masih ada tagihan aktif.
     */
    public function deactivate(int $id): RedirectResponse
    {
        try {
            $pelanggan = $this->service->deactivate($id);

            return back()->with(
                'success',
                "Pelanggan {$pelanggan->nama} berhasil dinonaktifkan."
            );

        } catch (PelangganMasihMemilikiTagihanException $e) {
            return back()->with('error', $e->getMessage());

        } catch (PelangganNotFoundException $e) {
            return redirect()
                ->route('admin.pelanggan.index')
                ->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menonaktifkan pelanggan: ' . $e->getMessage());
        }
    }

    /**
     * Aktifkan kembali pelanggan.
     * Endpoint: PATCH /admin/pelanggan/{id}/activate
     */
    public function activate(int $id): RedirectResponse
    {
        try {
            $pelanggan = $this->service->activate($id);

            return back()->with(
                'success',
                "Pelanggan {$pelanggan->nama} berhasil diaktifkan kembali."
            );

        } catch (PelangganNotFoundException $e) {
            return redirect()
                ->route('admin.pelanggan.index')
                ->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengaktifkan pelanggan: ' . $e->getMessage());
        }
    }
}
