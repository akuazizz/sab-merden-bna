<?php

namespace App\Modules\Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Base class untuk semua domain event di sistem SAB Merden.
 *
 * Semua event per-modul harus extend class ini agar konsisten
 * dalam metadata (eventName, aggregateType, aggregateId).
 */
abstract class DomainEvent
{
    use Dispatchable, SerializesModels;

    public readonly string $occurredAt;

    public function __construct()
    {
        $this->occurredAt = now()->toISOString();
    }

    /**
     * Nama event untuk dicatat di event_logs.
     */
    abstract public function eventName(): string;

    /**
     * Tipe aggregate yang terkait (e.g. 'Tagihan', 'Transaksi').
     */
    abstract public function aggregateType(): string;

    /**
     * ID dari aggregate yang terkait.
     */
    abstract public function aggregateId(): int;

    /**
     * Payload yang dicatat di event_logs.
     */
    abstract public function toPayload(): array;
}
