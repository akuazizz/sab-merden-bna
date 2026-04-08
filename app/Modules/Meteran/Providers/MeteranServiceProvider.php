<?php

namespace App\Modules\Meteran\Providers;

use App\Modules\Meteran\Repositories\MeteranRepository;
use App\Modules\Meteran\Services\MeteranService;
use App\Modules\Shared\Contracts\BaseModuleServiceProvider;

class MeteranServiceProvider extends BaseModuleServiceProvider
{
    protected array $repositories = [];
    protected array $services     = [];

    protected function registerModuleBindings(): void
    {
        $this->app->singleton(MeteranRepository::class);
        $this->app->singleton(MeteranService::class);
    }

    public function boot(): void
    {
        //
    }
}
