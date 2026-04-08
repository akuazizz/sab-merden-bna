<?php

namespace App\Modules\Shared\Models;

use App\Models\User;
use App\Modules\Shared\Enums\EventChannel;
use App\Modules\Shared\Enums\EventLogStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLog extends Model
{
    protected $table = 'event_logs';

    /**
     * Append-only — tidak ada updated_at.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'event_name',
        'aggregate_type',
        'aggregate_id',
        'payload',
        'user_id',
        'channel',
        'status',
        'error_message',
    ];

    protected $casts = [
        'payload'    => 'array',
        'channel'    => EventChannel::class,
        'status'     => EventLogStatus::class,
        'created_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * User yang men-trigger event (nullable — bisa dari sistem/scheduler).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Factory / static constructor ─────────────────────────────────

    /**
     * Helper untuk catat event berhasil dikonsumsi.
     */
    public static function catat(
        string $eventName,
        string $aggregateType,
        int    $aggregateId,
        array  $payload = [],
        ?int   $userId = null,
    ): self {
        return self::create([
            'event_name'     => $eventName,
            'aggregate_type' => $aggregateType,
            'aggregate_id'   => $aggregateId,
            'payload'        => $payload,
            'user_id'        => $userId,
            'channel'        => EventChannel::Internal,
            'status'         => EventLogStatus::Consumed,
        ]);
    }

    /**
     * Helper untuk catat event yang gagal.
     */
    public static function catetGagal(
        string $eventName,
        string $aggregateType,
        int    $aggregateId,
        string $errorMessage,
        array  $payload = [],
    ): self {
        return self::create([
            'event_name'     => $eventName,
            'aggregate_type' => $aggregateType,
            'aggregate_id'   => $aggregateId,
            'payload'        => $payload,
            'channel'        => EventChannel::Internal,
            'status'         => EventLogStatus::Failed,
            'error_message'  => $errorMessage,
        ]);
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeFailed($query)
    {
        return $query->where('status', EventLogStatus::Failed);
    }

    public function scopeForAggregate($query, string $type, int $id)
    {
        return $query->where('aggregate_type', $type)
            ->where('aggregate_id', $id);
    }
}
