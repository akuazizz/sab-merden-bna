<?php

namespace App\Modules\Meteran\Exceptions;

use RuntimeException;

class MeteranSudahDiinputException extends RuntimeException
{
    public function __construct(string $periode)
    {
        parent::__construct(
            "Pembacaan meteran untuk periode '{$periode}' sudah ada.",
            409,
        );
    }
}
