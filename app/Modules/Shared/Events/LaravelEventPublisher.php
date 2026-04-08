<?php

namespace App\Modules\Shared\Events;

use App\Modules\Shared\Contracts\EventPublisherInterface;
use Illuminate\Support\Facades\Log;

/**
 * Implementasi default EventPublisher menggunakan Laravel Event System.
 *
 * Saat RabbitMQ diaktifkan, buat RabbitMQPublisher yang implements
 * EventPublisherInterface, lalu swap binding di SharedServiceProvider
 * —- tidak ada perubahan di consumer manapun.
 */
class LaravelEventPublisher implements EventPublisherInterface
{
    public function publish(string $exchange, string $routingKey, array $payload): void
    {
        // Untuk sekarang, log sebagai trace dan dispatch via Laravel Queue
        Log::debug("[EventPublisher] exchange={$exchange} routing={$routingKey}", $payload);

        // Dispatch raw payload sebagai generic event jika diperlukan
        // (modul-modul akan dispatch typed event mereka sendiri via event())
    }
}
