<?php

namespace App\Modules\Tagihan\Listeners;

use App\Modules\Meteran\Events\MeteranDibaca;
use App\Modules\Tagihan\Exceptions\TagihanSudahAdaException;
use App\Modules\Tagihan\Services\TagihanService;
use Illuminate\Support\Facades\Log;

/**
 * Listener: saat MeteranDibaca event diterima,
 * otomatis generate tagihan dari meter reading tsb.
 *
 * QUEUE_CONNECTION=sync → berjalan synchronous (langsung).
 * QUEUE_CONNECTION=database → berjalan async via queue worker.
 */
class GenerateTagihanListener
{
    public function __construct(
        private readonly TagihanService $tagihanService,
    ) {}

    public function handle(MeteranDibaca $event): void
    {
        $reading = $event->reading;

        try {
            $tagihan = $this->tagihanService->generate($reading);

            Log::info("Tagihan berhasil digenerate dari meter reading.", [
                'reading_id'    => $reading->id,
                'tagihan_id'    => $tagihan->id,
                'nomor_tagihan' => $tagihan->nomor_tagihan,
                'total'         => $tagihan->total_tagihan,
            ]);

        } catch (TagihanSudahAdaException $e) {
            // Idempotency: tagihan sudah ada → skip, bukan error
            Log::info("Tagihan sudah ada untuk reading {$reading->id}. Skip generate.");

        } catch (\Throwable $e) {
            Log::error("Gagal generate tagihan dari reading {$reading->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e; // re-throw agar job bisa di-retry (jika pakai queue)
        }
    }
}
