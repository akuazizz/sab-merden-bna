<?php

namespace App\Modules\Pelanggan\Exceptions;

use RuntimeException;

class PelangganNotFoundException extends RuntimeException
{
    public function __construct(int|string $identifier = '')
    {
        $msg = $identifier
            ? "Pelanggan '{$identifier}' tidak ditemukan."
            : 'Pelanggan tidak ditemukan.';

        parent::__construct($msg, 404);
    }
}
