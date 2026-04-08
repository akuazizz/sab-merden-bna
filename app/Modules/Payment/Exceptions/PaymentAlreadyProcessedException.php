<?php

namespace App\Modules\Payment\Exceptions;

use RuntimeException;

class PaymentAlreadyProcessedException extends RuntimeException
{
    public function __construct(string $kode)
    {
        parent::__construct("Pembayaran '{$kode}' sudah diproses sebelumnya.", 409);
    }
}
