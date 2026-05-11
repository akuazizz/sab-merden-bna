<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQConsumerCommand extends Command
{
    protected $signature = 'rabbitmq:consume {queue?} {--all : Consume all queues defined in config}';
    protected $description = 'Consume messages from a specific RabbitMQ queue (or all queues with --all)';

    public function handle()
    {
        $allQueues  = config('rabbitmq.queues', []);
        $consumeAll = $this->option('all');
        $queueName  = $this->argument('queue');

        // Validasi argumen
        if (!$consumeAll && !$queueName) {
            $this->error('Tentukan nama queue atau gunakan flag --all untuk consume semua queue.');
            $this->line('Contoh:');
            $this->line('  php artisan rabbitmq:consume generate-tagihan');
            $this->line('  php artisan rabbitmq:consume --all');
            $this->newLine();
            $this->line('Queue yang tersedia:');
            foreach (array_keys($allQueues) as $q) {
                $this->line("  - {$q}");
            }
            return 1;
        }

        // Tentukan queue mana yang akan di-consume
        $targetQueues = $consumeAll
            ? $allQueues
            : (isset($allQueues[$queueName])
                ? [$queueName => $allQueues[$queueName]]
                : null);

        if ($targetQueues === null) {
            $this->error("Queue '{$queueName}' tidak terdefinisi di config/rabbitmq.php");
            $this->line('Queue yang tersedia: ' . implode(', ', array_keys($allQueues)));
            return 1;
        }

        try {
            $connection = new AMQPStreamConnection(
                config('rabbitmq.host'),
                config('rabbitmq.port'),
                config('rabbitmq.user'),
                config('rabbitmq.password'),
                config('rabbitmq.vhost')
            );
            $channel = $connection->channel();

            // Bind semua queue yang ditarget ke satu channel
            foreach ($targetQueues as $name => $queueConfig) {
                $exchange   = $queueConfig['exchange'];
                $routingKey = $queueConfig['routing_key'];

                // Pastikan exchange ada (idempotent)
                $channel->exchange_declare($exchange, 'topic', false, true, false);

                // Deklarasikan queue dan bind ke exchange
                $channel->queue_declare($name, false, true, false, false);
                $channel->queue_bind($name, $exchange, $routingKey);

                $callback = function (AMQPMessage $msg) use ($name, $channel) {
                    $this->line('');
                    $this->info("[✓] [{$name}] Message diterima:");
                    $data = json_decode($msg->body, true);
                    $this->line('  Exchange    : ' . ($data['exchange']    ?? '-'));
                    $this->line('  Routing Key : ' . ($data['routing_key'] ?? '-'));
                    $this->line('  Payload     : ' . json_encode(
                        $data['payload'] ?? $data,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                    ));

                    try {
                        $deliveryTag = $msg->delivery_info['delivery_tag'] ?? null;
                        if ($deliveryTag) {
                            $channel->basic_ack($deliveryTag);
                        }
                        $this->info("  [ack] Message diproses.");
                    } catch (\Throwable $e) {
                        $this->error("  Error ack: " . $e->getMessage());
                    }
                };

                $channel->basic_consume($name, '', false, false, false, false, $callback);
                $this->info("  ✓ Listening: [{$name}] → exchange={$exchange} routing={$routingKey}");
            }

            $this->newLine();
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info(' SAB Merden RabbitMQ Consumer aktif');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line(' Menunggu messages... Tekan CTRL+C untuk berhenti.');
            $this->newLine();

            // Loop tanpa batas — wait() block sampai ada message atau timeout
            while (true) {
                try {
                    $channel->wait(null, false, 60); // block max 60 detik
                } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                    // Timeout normal (tidak ada message 60 detik) → lanjut loop
                    continue;
                }
            }

            $channel->close();
            $connection->close();

        } catch (\Exception $e) {
            $this->error('Gagal menghubungkan ke RabbitMQ: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
