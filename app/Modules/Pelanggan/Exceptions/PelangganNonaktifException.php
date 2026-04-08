<?php

namespace App\Modules\Pelanggan\Exceptions;

use RuntimeException;

class PelangganNonaktifException extends RuntimeException
{
    public function __construct(string $nama = '')
    {
        $msg = $nama
            ? "Pelanggan '{$nama}' berstatus nonaktif."
            : 'Pelanggan tidak aktif.';

        parent::__construct($msg, 422);
    }
}
