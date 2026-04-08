<?php

namespace App\Modules\Tagihan\Exceptions;

use App\Modules\Tagihan\Enums\TagihanStatus;
use RuntimeException;

class TransisiStatusTidakValidException extends RuntimeException
{
    public function __construct(TagihanStatus $dari, TagihanStatus $ke)
    {
        parent::__construct(
            "Tagihan tidak bisa berpindah dari status '{$dari->label()}' ke '{$ke->label()}'. " .
            "Transisi yang diizinkan: " .
            implode(', ', array_map(fn(TagihanStatus $s) => $s->label(), $dari->allowedTransitions())),
            422,
        );
    }
}
