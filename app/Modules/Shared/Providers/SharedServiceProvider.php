<?php

namespace App\Modules\Shared\Providers;

use App\Modules\Shared\Contracts\EventPublisherInterface;
use App\Modules\Shared\Events\RabbitMQPublisher;
use Illuminate\Support\ServiceProvider;

/**
 * SharedServiceProvider — mendaftarkan binding shared/global
 * yang dipakai oleh semua modul.
 *
 * Binding EventPublisherInterface ke RabbitMQPublisher.
 * Jika RabbitMQ tidak tersedia (development tanpa broker),
 * ganti ke LaravelEventPublisher di baris ini saja.
 */
class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // RabbitMQ aktif — semua domain event dikirim via AMQP broker
        $this->app->singleton(
            EventPublisherInterface::class,
            RabbitMQPublisher::class,
        );
    }

    public function boot(): void
    {
        //
    }
}
