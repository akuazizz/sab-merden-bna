<?php

namespace App\Modules\Shared\Enums;

enum EventLogStatus: string
{
    case Dispatched = 'dispatched';
    case Consumed   = 'consumed';
    case Failed     = 'failed';
}
