<?php

namespace App\Modules\Laporan\Providers;

use App\Modules\Shared\Contracts\BaseModuleServiceProvider;

// use App\Modules\Laporan\Contracts\LaporanServiceInterface;
// use App\Modules\Laporan\Services\LaporanService;
// use App\Modules\Laporan\Contracts\LaporanRepositoryInterface;
// use App\Modules\Laporan\Repositories\LaporanRepository;

class LaporanServiceProvider extends BaseModuleServiceProvider
{
    protected array $repositories = [
        // LaporanRepositoryInterface::class => LaporanRepository::class,
    ];

    protected array $services = [
        // LaporanServiceInterface::class => LaporanService::class,
    ];

    public function boot(): void
    {
        //
    }
}
