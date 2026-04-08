<?php

namespace App\Modules\Shared\Contracts;

/**
 * Kontrak untuk event publishing.
 *
 * Saat ini diimplementasikan oleh LaravelEventPublisher (internal queue).
 * Di masa depan bisa di-swap ke RabbitMQPublisher tanpa mengubah consumer.
 */
interface EventPublisherInterface
{
    /**
     * Publish sebuah event ke channel tertentu.
     *
     * @param  string  $exchange   Nama exchange / channel (e.g. 'sab.payment')
     * @param  string  $routingKey Routing key / queue name (e.g. 'payment.callback')
     * @param  array   $payload    Data event yang akan dipublish
     */
    public function publish(string $exchange, string $routingKey, array $payload): void;
}
