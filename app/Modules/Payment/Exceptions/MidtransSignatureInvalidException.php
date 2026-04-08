<?php

namespace App\Modules\Payment\Exceptions;

use RuntimeException;

class MidtransSignatureInvalidException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Signature Midtrans tidak valid. Request ditolak.', 403);
    }
}
