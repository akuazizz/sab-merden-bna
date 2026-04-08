<?php

namespace App\Providers;

use App\Modules\Meteran\Events\MeteranDibaca;
use App\Modules\Tagihan\Listeners\GenerateTagihanListener;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Blade anonymous component paths ──────────────────────────────
        // <x-layouts.admin>     → resources/views/layouts/admin.blade.php
        // <x-layouts.guest>     → resources/views/layouts/guest.blade.php
        // <x-layouts.pelanggan> → resources/views/layouts/pelanggan.blade.php
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');

        // ── Domain Events ────────────────────────────────────────────────
        // MeteranDibaca → auto-generate tagihan
        Event::listen(MeteranDibaca::class, GenerateTagihanListener::class);
    }
}
