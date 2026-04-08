<?php

namespace App\Modules\Tagihan\Exceptions;

use RuntimeException;

class TagihanSudahAdaException extends RuntimeException
{
    public function __construct(int $meterReadingId)
    {
        parent::__construct(
            "Tagihan untuk meter reading #{$meterReadingId} sudah dibuat.",
            409,
        );
    }
}
