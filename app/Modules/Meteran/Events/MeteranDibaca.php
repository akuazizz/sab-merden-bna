<?php

namespace App\Modules\Meteran\Events;

use App\Modules\Meteran\Models\MeteranReading;
use App\Modules\Shared\Events\DomainEvent;

/**
 * Di-dispatch setelah petugas berhasil menyimpan pembacaan meteran.
 * Listener: GenerateTagihanListener
 */
class MeteranDibaca extends DomainEvent
{
    public function __construct(
        public readonly MeteranReading $reading,
    ) {
        parent::__construct();
    }

    public function eventName(): string    { return 'MeteranDibaca'; }
    public function aggregateType(): string { return 'MeteranReading'; }
    public function aggregateId(): int     { return $this->reading->id; }

    public function toPayload(): array
    {
        return [
            'pelanggan_id'  => $this->reading->pelanggan_id,
            'periode'       => $this->reading->periode,
            'pemakaian'     => (float) $this->reading->pemakaian,
            'dicatat_oleh'  => $this->reading->dicatat_oleh,
        ];
    }
}
