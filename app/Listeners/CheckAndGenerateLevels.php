<?php

namespace App\Listeners;

use App\Events\PlayerLeveledUpEvent;
use App\Jobs\GenerateNextLevelsJob;
use App\Jobs\SendTelegramNotification;
use App\Models\Question;

class CheckAndGenerateLevels
{
    public function handle(PlayerLeveledUpEvent $event): void
    {
        $maxLevelTersedia = (int) (Question::query()->max('level') ?? 0);
        $levelPemain = $event->player->current_level;

        if ($maxLevelTersedia === 0 || $levelPemain < ($maxLevelTersedia - 2)) {
            return;
        }

        for ($i = 1; $i <= 3; $i++) {
            GenerateNextLevelsJob::dispatch($maxLevelTersedia + $i)
                ->onQueue('ai-generate');
        }

        SendTelegramNotification::dispatch(
            "⚙️ [SYSTEM] Pemain mencapai Level {$levelPemain}. Memicu generasi Level " . ($maxLevelTersedia + 1) . '-' . ($maxLevelTersedia + 3)
        )->onQueue('telegram');
    }
}
