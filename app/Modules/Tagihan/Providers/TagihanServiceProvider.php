<?php

namespace App\Modules\Tagihan\Providers;

use App\Modules\Shared\Contracts\BaseModuleServiceProvider;
use App\Modules\Shared\Repositories\PengaturanRepository;
use App\Modules\Tagihan\Repositories\TagihanRepository;
use App\Modules\Tagihan\Services\TagihanService;

class TagihanServiceProvider extends BaseModuleServiceProvider
{
    protected array $repositories = [];
    protected array $services     = [];

    protected function registerModuleBindings(): void
    {
        $this->app->singleton(PengaturanRepository::class);
        $this->app->singleton(TagihanRepository::class);
        $this->app->singleton(TagihanService::class);
    }

    public function boot(): void
    {
        //
    }
}
