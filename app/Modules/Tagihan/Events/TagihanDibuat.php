<?php

namespace App\Modules\Tagihan\Events;

use App\Modules\Shared\Events\DomainEvent;
use App\Modules\Tagihan\Models\Tagihan;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Di-dispatch setelah tagihan berhasil dibuat dari meter reading.
 * Listener: KirimNotifikasiTagihan (queue: 'notifications')
 */
class TagihanDibuat extends DomainEvent implements ShouldQueue
{
    public function __construct(
        public readonly Tagihan $tagihan,
    ) {
        parent::__construct();
    }

    public function eventName(): string     { return 'TagihanDibuat'; }
    public function aggregateType(): string { return 'Tagihan'; }
    public function aggregateId(): int      { return $this->tagihan->id; }

    public function toPayload(): array
    {
        return [
            'nomor_tagihan'  => $this->tagihan->nomor_tagihan,
            'pelanggan_id'   => $this->tagihan->pelanggan_id,
            'periode'        => $this->tagihan->periode,
            'total_tagihan'  => (float) $this->tagihan->total_tagihan,
            'jatuh_tempo'    => $this->tagihan->tanggal_jatuh_tempo?->toDateString(),
        ];
    }
}
