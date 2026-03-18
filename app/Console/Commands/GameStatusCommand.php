<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\Question;
use Illuminate\Console\Command;

class GameStatusCommand extends Command
{
    protected $signature = 'game:status';

    protected $description = 'Ringkasan status game: pemain, level, soal, dan leaderboard singkat';

    public function handle(): int
    {
        $totalPlayers = Player::query()->count();
        $activeToday = Player::query()->whereDate('last_active_at', now()->toDateString())->count();
        $maxPlayerLevel = (int) (Player::query()->max('current_level') ?? 0);
        $maxQuestionLevel = (int) (Question::query()->max('level') ?? 0);
        $totalQuestions = Question::query()->count();

        $this->info('=== Dunia Anin Status ===');
        $this->line("Total pemain        : {$totalPlayers}");
        $this->line("Pemain aktif hari ini: {$activeToday}");
        $this->line("Level tertinggi pemain: {$maxPlayerLevel}");
        $this->line("Level tertinggi bank soal: {$maxQuestionLevel}");
        $this->line("Total bank soal     : {$totalQuestions}");

        $perLevel = Question::query()
            ->selectRaw('level, COUNT(*) as total')
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        if ($perLevel->isNotEmpty()) {
            $this->newLine();
            $this->info('Soal per level:');

            foreach ($perLevel as $row) {
                $this->line("- Level {$row->level}: {$row->total}");
            }
        }

        $topPlayers = Player::query()
            ->orderByDesc('total_score')
            ->orderByDesc('current_level')
            ->limit(5)
            ->get();

        if ($topPlayers->isNotEmpty()) {
            $this->newLine();
            $this->info('Top 5 pemain:');

            foreach ($topPlayers as $idx => $player) {
                $rank = $idx + 1;
                $this->line("{$rank}. {$player->username} | Level {$player->current_level} | {$player->total_score} poin");
            }
        }

        return self::SUCCESS;
    }
}
