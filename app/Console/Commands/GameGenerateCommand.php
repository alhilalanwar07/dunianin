<?php

namespace App\Console\Commands;

use App\Jobs\GenerateNextLevelsJob;
use Illuminate\Console\Command;

class GameGenerateCommand extends Command
{
    protected $signature = 'game:generate {level : Level target} {--to= : Level akhir (opsional, untuk range)} {--count=15 : Jumlah soal} {--sync : Jalankan sinkron}';

    protected $description = 'Generate bank soal game untuk level tertentu';

    public function handle(): int
    {
        $level = (int) $this->argument('level');
        $to = $this->option('to') !== null ? (int) $this->option('to') : $level;
        $count = max(1, (int) $this->option('count'));

        if ($level < 1) {
            $this->error('Level minimal 1.');

            return self::FAILURE;
        }

        if ($to < $level) {
            $this->error('Nilai --to tidak boleh lebih kecil dari level awal.');

            return self::FAILURE;
        }

        for ($currentLevel = $level; $currentLevel <= $to; $currentLevel++) {
            if ((bool) $this->option('sync')) {
                dispatch_sync(new GenerateNextLevelsJob($currentLevel, $count));
                $this->info("Soal level {$currentLevel} selesai dibuat secara sinkron.");

                continue;
            }

            GenerateNextLevelsJob::dispatch($currentLevel, $count)->onQueue('ai-generate');
            $this->info("Job generate level {$currentLevel} dikirim ke queue ai-generate.");
        }

        return self::SUCCESS;
    }
}
