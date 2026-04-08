<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQConsumerCommand extends Command
{
    protected $signature = 'rabbitmq:consume {queue}';
    protected $description = 'Consume messages from a specific RabbitMQ queue';

    public function handle()
    {
        $queueName = $this->argument('queue');
        $this->info("Starting RabbitMQ Consumer for queue: {$queueName}");

        try {
            $connection = new AMQPStreamConnection(
                config('rabbitmq.host'),
                config('rabbitmq.port'),
                config('rabbitmq.user'),
                config('rabbitmq.password'),
                config('rabbitmq.vhost')
            );
            $channel = $connection->channel();

            // Setup queues according to config mapping
            $queues = config('rabbitmq.queues', []);
            if (!isset($queues[$queueName])) {
                $this->error("Queue '{$queueName}' is not defined in config/rabbitmq.php");
                return 1;
            }

            $queueConfig = $queues[$queueName];
            $exchange = $queueConfig['exchange'];
            $routingKey = $queueConfig['routing_key'];

            // Declare queue and bind to exchange
            $channel->queue_declare($queueName, false, true, false, false);
            $channel->queue_bind($queueName, $exchange, $routingKey);

            $this->info("Waiting for messages. To exit press CTRL+C");

            $callback = function (AMQPMessage $msg) use ($queueName) {
                $this->info("[x] Received in {$queueName}:");
                $this->line($msg->body);

                try {
                    $payload = json_decode($msg->body, true);
                    
                    // Here you would dispatch internal Jobs or Listeners based on the queue
                    // For example:
                    // if ($queueName === 'generate-tagihan') {
                    //     // trigger something manually
                    // }

                    $msg->ack();
                } catch (\Throwable $e) {
                    $this->error("Error processing message: " . $e->getMessage());
                    // Optionally: $msg->nack(true); // requeue
                }
            };

            $channel->basic_consume($queueName, '', false, false, false, false, $callback);

            while ($channel->is_open()) {
                $channel->wait();
            }

            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            $this->error('Failed to start consumer: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
