<?php

namespace App\Modules\Pelanggan\Exceptions;

use RuntimeException;

class PelangganMasihMemilikiTagihanException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Pelanggan masih memiliki tagihan aktif. Selesaikan atau batalkan tagihan terlebih dahulu.',
            422,
        );
    }
}
