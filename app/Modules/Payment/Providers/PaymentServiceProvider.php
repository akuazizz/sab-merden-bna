<?php

namespace App\Modules\Payment\Providers;

use App\Modules\Payment\Repositories\TransaksiRepository;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Shared\Contracts\BaseModuleServiceProvider;
use App\Modules\Shared\Repositories\PengaturanRepository;
use App\Modules\Tagihan\Services\TagihanService;

class PaymentServiceProvider extends BaseModuleServiceProvider
{
    protected array $repositories = [];
    protected array $services     = [];

    protected function registerModuleBindings(): void
    {
        $this->app->singleton(TransaksiRepository::class);
        $this->app->singleton(PaymentService::class);
    }

    public function boot(): void
    {
        //
    }
}
