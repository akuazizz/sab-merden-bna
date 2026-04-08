<?php

return [
    App\Providers\AppServiceProvider::class,

    // ── Shared (harus pertama — binding global) ──────────────────────
    App\Modules\Shared\Providers\SharedServiceProvider::class,

    // ── Modules ──────────────────────────────────────────────────────
    App\Modules\Pelanggan\Providers\PelangganServiceProvider::class,
    App\Modules\Meteran\Providers\MeteranServiceProvider::class,
    App\Modules\Tagihan\Providers\TagihanServiceProvider::class,
    App\Modules\Payment\Providers\PaymentServiceProvider::class,
    App\Modules\Laporan\Providers\LaporanServiceProvider::class,
];
