<?php

use App\Http\Controllers\ProfileController;
use App\Modules\Admin\Controllers\AdminDashboardController;
use App\Modules\Admin\Controllers\AdminLaporanController;
use App\Modules\Meteran\Controllers\Admin\MeteranController;
use App\Modules\Payment\Controllers\Admin\AdminTransaksiController;
use App\Modules\Payment\Controllers\Admin\PaymentController;
use App\Modules\Payment\Controllers\Webhook\MidtransWebhookController;
use App\Modules\Pelanggan\Controllers\Admin\PelangganController;
use App\Modules\Portal\Controllers\PortalDashboardController;
use App\Modules\Portal\Controllers\PortalTagihanController;
use App\Modules\Portal\Controllers\RiwayatPembayaranController;
use App\Modules\Tagihan\Controllers\Admin\TagihanController;
use Illuminate\Support\Facades\Route;

// ── Public ───────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// ═════════════════════════════════════════════════════════════════════════════
// ADMIN PORTAL — middleware: auth + role:admin
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // ── Pelanggan ────────────────────────────────────────────────────────
        Route::resource('pelanggan', PelangganController::class)
            ->parameters(['pelanggan' => 'id'])
            ->except(['show']);

        Route::get('pelanggan/{id}', [PelangganController::class, 'show'])
            ->name('pelanggan.show');

        Route::patch('pelanggan/{id}/deactivate', [PelangganController::class, 'deactivate'])
            ->name('pelanggan.deactivate');
        Route::patch('pelanggan/{id}/activate', [PelangganController::class, 'activate'])
            ->name('pelanggan.activate');

        // ── Meteran ──────────────────────────────────────────────────────────
        Route::resource('meteran', MeteranController::class)
            ->parameters(['meteran' => 'id'])
            ->except(['destroy']);
        Route::get('meteran/suggestion', [MeteranController::class, 'suggestion'])
            ->name('meteran.suggestion');

        // ── Tagihan ───────────────────────────────────────────────────────────
        Route::resource('tagihan', TagihanController::class)
            ->parameters(['tagihan' => 'id'])
            ->only(['index', 'show']);
        Route::patch('tagihan/{id}/void',  [TagihanController::class, 'void'])->name('tagihan.void');
        Route::patch('tagihan/{id}/lunas', [TagihanController::class, 'tandaiLunas'])->name('tagihan.lunas');
        Route::post('tagihan/mark-overdue',[TagihanController::class, 'markOverdue'])->name('tagihan.mark-overdue');

        // ── Pembayaran / Transaksi ────────────────────────────────────────────
        Route::get('pembayaran', [AdminTransaksiController::class, 'index'])->name('pembayaran.index');
        Route::get('pembayaran/{id}', [AdminTransaksiController::class, 'show'])->name('pembayaran.show');

        // ── Laporan ───────────────────────────────────────────────────────────
        Route::get('laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/download', [AdminLaporanController::class, 'download'])->name('laporan.download');
    });

// ═════════════════════════════════════════════════════════════════════════════
// PELANGGAN PORTAL — middleware: auth + role:pelanggan
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:pelanggan'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {

        Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');

        // ── Tagihan pelanggan ─────────────────────────────────────────────────
        Route::get('/tagihan', [PortalTagihanController::class, 'index'])->name('tagihan.index');
        Route::get('/tagihan/{id}', [PortalTagihanController::class, 'show'])->name('tagihan.show');

        // ── Bayar ─────────────────────────────────────────────────────────────
        Route::post('/tagihan/{id}/bayar', [PaymentController::class, 'initiate'])->name('tagihan.bayar');
        Route::get('/tagihan/{id}/bayar/status', [PaymentController::class, 'status'])->name('tagihan.bayar.status');

        // ── Riwayat pembayaran ────────────────────────────────────────────────
        Route::get('/riwayat', [RiwayatPembayaranController::class, 'index'])->name('riwayat.index');
        Route::get('/riwayat/{id}', [RiwayatPembayaranController::class, 'show'])->name('riwayat.show');
    });

// ═════════════════════════════════════════════════════════════════════════════
// WEBHOOK — Tanpa auth, tanpa CSRF (dikecualikan di bootstrap/app.php)
// ═════════════════════════════════════════════════════════════════════════════
Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle'])
    ->name('webhook.midtrans');

