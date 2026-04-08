<?php

namespace App\Modules\Meteran\Exceptions;

use RuntimeException;

class KubikTidakValidException extends RuntimeException
{
    public function __construct(float $awal, float $akhir)
    {
        parent::__construct(
            "Kubik akhir ({$akhir} m³) tidak boleh lebih kecil dari kubik awal ({$awal} m³).",
            422,
        );
    }
}
