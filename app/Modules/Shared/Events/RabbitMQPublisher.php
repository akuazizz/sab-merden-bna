<?php

namespace App\Modules\Shared\Events;

use App\Modules\Shared\Contracts\EventPublisherInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

/**
 * Implementasi EventPublisher menggunakan RabbitMQ (AMQP).
 *
 * Setiap domain event di-publish ke RabbitMQ exchange bertipe 'topic',
 * sehingga consumer bisa subscribe berdasarkan routing key.
 *
 * Contoh flow:
 *   MeteranDibaca → exchange: sab.events → routing: meteran.dibaca
 *   TagihanDibuat → exchange: sab.events → routing: tagihan.dibuat
 *   PaymentSukses → exchange: sab.payment → routing: payment.callback
 */
class RabbitMQPublisher implements EventPublisherInterface
{
    private ?AMQPStreamConnection $connection = null;
    private ?AMQPChannel $channel = null;

    public function __construct()
    {
        $this->connect();
    }

    /**
     * Publish event ke RabbitMQ exchange.
     *
     * @param  string  $exchange   Nama exchange (e.g. 'sab.events', 'sab.payment')
     * @param  string  $routingKey Routing key (e.g. 'meteran.dibaca', 'tagihan.dibuat')
     * @param  array   $payload    Data event
     */
    public function publish(string $exchange, string $routingKey, array $payload): void
    {
        try {
            // Pastikan koneksi masih aktif
            if (!$this->connection || !$this->connection->isConnected()) {
                $this->connect();
            }

            // Deklarasikan exchange bertipe 'topic' (idempotent — aman dipanggil berulang)
            $this->channel->exchange_declare(
                exchange: $exchange,
                type: 'topic',
                passive: false,
                durable: true,      // survive broker restart
                auto_delete: false,
            );

            // Buat pesan AMQP
            $body = json_encode([
                'exchange'    => $exchange,
                'routing_key' => $routingKey,
                'payload'     => $payload,
                'published_at' => now()->toISOString(),
                'app'         => config('app.name'),
            ]);

            $message = new AMQPMessage($body, [
                'content_type'  => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, // tahan restart
            ]);

            // Publish ke exchange dengan routing key
            $this->channel->basic_publish($message, $exchange, $routingKey);

            Log::info("[RabbitMQ] Published: exchange={$exchange} routing={$routingKey}", $payload);

        } catch (\Throwable $e) {
            Log::error("[RabbitMQ] Gagal publish event: {$e->getMessage()}", [
                'exchange'    => $exchange,
                'routing_key' => $routingKey,
                'payload'     => $payload,
            ]);
            // Tidak throw agar sistem utama tidak terganggu jika RabbitMQ down
        }
    }

    /**
     * Buka koneksi dan channel ke RabbitMQ broker.
     */
    private function connect(): void
    {
        try {
            $this->connection = new AMQPStreamConnection(
                host:     config('rabbitmq.host', '127.0.0.1'),
                port:     config('rabbitmq.port', 5672),
                user:     config('rabbitmq.user', 'guest'),
                password: config('rabbitmq.password', 'guest'),
                vhost:    config('rabbitmq.vhost', '/'),
            );

            $this->channel = $this->connection->channel();

            Log::debug('[RabbitMQ] Koneksi berhasil ke broker.');

        } catch (\Throwable $e) {
            Log::error('[RabbitMQ] Gagal konek ke broker: ' . $e->getMessage());
            $this->connection = null;
            $this->channel = null;
        }
    }

    /**
     * Tutup channel dan koneksi saat publisher di-destruct.
     */
    public function __destruct()
    {
        try {
            $this->channel?->close();
            $this->connection?->close();
        } catch (\Throwable) {
            // silent close
        }
    }
}
