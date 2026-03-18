<?php

namespace App\Console\Commands;

use App\Jobs\GenerateNextLevelsJob;
use Illuminate\Console\Command;

class GameSeedInitialCommand extends Command
{
    protected $signature = 'game:seed-initial
                            {--from=1 : Level awal}
                            {--to=3 : Level akhir}
                            {--count=15 : Jumlah soal per level}
                            {--sync : Jalankan sinkron}';

    protected $description = 'Bootstrap soal awal multi-level (default Level 1-3)';

    public function handle(): int
    {
        $from = max(1, (int) $this->option('from'));
        $to = max($from, (int) $this->option('to'));
        $count = max(1, (int) $this->option('count'));
        $sync = (bool) $this->option('sync');

        for ($level = $from; $level <= $to; $level++) {
            if ($sync) {
                dispatch_sync(new GenerateNextLevelsJob($level, $count));
                $this->line("[SYNC] Level {$level} selesai.");

                continue;
            }

            GenerateNextLevelsJob::dispatch($level, $count)->onQueue('ai-generate');
            $this->line("[QUEUE] Level {$level} dikirim.");
        }

        $this->info("Bootstrap selesai untuk Level {$from}-{$to}.");

        return self::SUCCESS;
    }
}
