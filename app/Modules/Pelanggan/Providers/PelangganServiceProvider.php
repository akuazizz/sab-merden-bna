<?php

namespace App\Modules\Pelanggan\Providers;

use App\Modules\Shared\Contracts\BaseModuleServiceProvider;
use App\Modules\Pelanggan\Repositories\PelangganRepository;
use App\Modules\Pelanggan\Services\PelangganService;

class PelangganServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Repository di-bind sebagai singleton agar tidak bikin instance baru
     * setiap injection — aman karena repository stateless.
     */
    protected array $repositories = [
        // Tidak ada interface untuk repository (concrete injection)
        // Binding dilakukan di registerModuleBindings
    ];

    protected array $services = [
        // PelangganService tidak butuh interface — inject concrete langsung
    ];

    protected function registerModuleBindings(): void
    {
        // Singleton: aman karena stateless
        $this->app->singleton(PelangganRepository::class);
        $this->app->singleton(PelangganService::class);
    }

    public function boot(): void
    {
        //
    }
}
