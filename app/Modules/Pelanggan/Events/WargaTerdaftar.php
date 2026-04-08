<?php

namespace App\Modules\Pelanggan\Events;

use App\Modules\Pelanggan\Models\Pelanggan;
use App\Modules\Shared\Events\DomainEvent;

/**
 * Di-dispatch setelah pelanggan baru berhasil didaftarkan.
 * Listener: CatatAktivitasWarga (logging)
 */
class WargaTerdaftar extends DomainEvent
{
    public function __construct(
        public readonly Pelanggan $pelanggan,
    ) {
        parent::__construct();
    }

    public function eventName(): string     { return 'WargaTerdaftar'; }
    public function aggregateType(): string { return 'Pelanggan'; }
    public function aggregateId(): int      { return $this->pelanggan->id; }

    public function toPayload(): array
    {
        return [
            'nomor_pelanggan' => $this->pelanggan->nomor_pelanggan,
            'nama'            => $this->pelanggan->nama,
            'tanggal_daftar'  => $this->pelanggan->tanggal_daftar->toDateString(),
        ];
    }
}
