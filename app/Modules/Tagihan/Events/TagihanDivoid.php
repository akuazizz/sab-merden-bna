<?php

namespace App\Modules\Tagihan\Events;

use App\Modules\Shared\Events\DomainEvent;
use App\Modules\Tagihan\Models\Tagihan;

/**
 * Di-dispatch saat admin mem-void sebuah tagihan.
 * Listener: BatalkanTransaksiPending — cancel semua transaksi pending terkait.
 */
class TagihanDivoid extends DomainEvent
{
    public function __construct(
        public readonly Tagihan $tagihan,
        public readonly ?string $alasan = null,
    ) {
        parent::__construct();
    }

    public function eventName(): string     { return 'TagihanDivoid'; }
    public function aggregateType(): string { return 'Tagihan'; }
    public function aggregateId(): int      { return $this->tagihan->id; }

    public function toPayload(): array
    {
        return [
            'nomor_tagihan' => $this->tagihan->nomor_tagihan,
            'pelanggan_id'  => $this->tagihan->pelanggan_id,
            'alasan'        => $this->alasan,
        ];
    }
}
