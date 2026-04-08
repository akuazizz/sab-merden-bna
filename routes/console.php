<?php

use App\Console\Commands\MarkTagihanOverdue;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduler ─────────────────────────────────────────────────────────────────

/**
 * Tandai tagihan jatuh tempo setiap hari jam 01:00.
 * withoutOverlapping() mencegah double-run jika proses sebelumnya belum selesai.
 */
Schedule::command('tagihan:mark-overdue')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

