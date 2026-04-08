<?php

namespace App\Modules\Shared\Providers;

use App\Modules\Shared\Contracts\EventPublisherInterface;
use App\Modules\Shared\Events\LaravelEventPublisher;
use Illuminate\Support\ServiceProvider;

/**
 * SharedServiceProvider — mendaftarkan binding shared/global
 * yang dipakai oleh semua modul.
 *
 * Didaftarkan paling awal di bootstrap/providers.php
 * agar tersedia saat provider modul lain di-boot.
 */
class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Binding EventPublisher — swap ke RabbitMQPublisher di sini jika saatnya
        $this->app->bind(
            EventPublisherInterface::class,
            LaravelEventPublisher::class,
        );
    }

    public function boot(): void
    {
        //
    }
}
