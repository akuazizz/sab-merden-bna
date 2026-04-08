<?php

namespace App\Modules\Shared\Enums;

enum EventChannel: string
{
    case Internal = 'internal';
    case RabbitMQ = 'rabbitmq';
}
