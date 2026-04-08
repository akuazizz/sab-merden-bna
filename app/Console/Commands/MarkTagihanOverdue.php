<?php

namespace App\Console\Commands;

use App\Modules\Tagihan\Services\TagihanService;
use Illuminate\Console\Command;

class MarkTagihanOverdue extends Command
{
    protected $signature   = 'tagihan:mark-overdue';
    protected $description = 'Tandai tagihan yang sudah melewati tanggal jatuh tempo sebagai "jatuh_tempo".';

    public function __construct(
        private readonly TagihanService $tagihanService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Memproses tagihan jatuh tempo...');

        try {
            $updated = $this->tagihanService->markOverdue();

            if ($updated > 0) {
                $this->info("✓ {$updated} tagihan berhasil ditandai jatuh tempo.");
            } else {
                $this->line('  Tidak ada tagihan yang perlu diperbarui.');
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('✗ Gagal: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
