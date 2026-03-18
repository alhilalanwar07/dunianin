<?php

namespace App\Livewire;

use App\Events\PlayerLeveledUpEvent;
use App\Jobs\SendTelegramNotification;
use App\Models\Player;
use App\Models\PlayerQuestionLog;
use App\Models\Question;
use App\Services\ChallengePayloadFactory;
use Illuminate\Support\Arr;
use Livewire\Component;

class GamePlay extends Component
{
    public string $state = 'map';

    public ?array $currentChallenge = null;

    public int $playerLevel = 1;

    public int $playerScore = 0;

    public int $correctCount = 0;

    public int $challengeVersion = 0;

    public array $recentQuestionIds = [];

    public ?string $statusMessage = null;

    public function mount()
    {
        $player = $this->player();

        if (! $player) {
            return $this->redirectRoute('home', navigate: true);
        }

        $this->playerLevel = $player->current_level;
        $this->playerScore = $player->total_score;
        $this->correctCount = PlayerQuestionLog::query()
            ->where('player_id', $player->id)
            ->where('level_saat_main', $this->playerLevel)
            ->where('is_correct', true)
            ->count() % 3;

        $this->dispatch('map-focus-active');
    }

    public function render()
    {
        return view('livewire.game-play', [
            'player' => $this->player(),
        ])->layout('layouts.app', ['title' => 'Arena - Dunia Anin']);
    }

    public function masukArena(int $level): void
    {
        if ($level !== $this->playerLevel) {
            return;
        }

        $this->state = 'arena';
        $this->ambilChallengeBaru();
    }

    public function kembaliKeMap(): void
    {
        $this->state = 'map';
    }

    public function ambilChallengeBaru(): void
    {
        $this->ensureLevelQuestions($this->playerLevel);

        $question = Question::query()
            ->where('level', $this->playerLevel)
            ->whereNotIn('id', $this->recentQuestionIds)
            ->inRandomOrder()
            ->first();

        if (! $question) {
            $this->recentQuestionIds = [];

            $question = Question::query()
                ->where('level', $this->playerLevel)
                ->inRandomOrder()
                ->first();
        }

        if (! $question) {
            $this->currentChallenge = null;
            $this->statusMessage = 'Belum ada soal untuk level ini.';

            return;
        }

        $payload = $question->payload;

        $this->currentChallenge = [
            'question_id' => $question->id,
            'instance_key' => $question->id . '-' . (++$this->challengeVersion),
            'engine' => $question->tipe_engine,
            'prompt' => Arr::get($payload, 'prompt', 'Mainkan tantangan ini.'),
            'payload' => $payload,
        ];

        $this->recentQuestionIds[] = $question->id;
        $this->recentQuestionIds = array_slice(array_values(array_unique($this->recentQuestionIds)), -5);
        $this->statusMessage = null;
    }

    public function challengeSelesai(string $questionId, bool $isCorrect, int $timeSpentMs): void
    {
        $player = $this->player();

        if (! $player) {
            $this->redirectRoute('home', navigate: true);

            return;
        }

        $oldLevel = $player->current_level;

        $scoreEarned = $isCorrect ? $this->hitungSkor($timeSpentMs) : 0;

        PlayerQuestionLog::query()->create([
            'player_id' => $player->id,
            'question_id' => $questionId,
            'level_saat_main' => $this->playerLevel,
            'is_correct' => $isCorrect,
            'score_earned' => $scoreEarned,
            'time_spent_ms' => max(1, $timeSpentMs),
        ]);

        if (! $isCorrect) {
            $this->statusMessage = 'Belum tepat. Kita ganti soal baru ya.';
            $this->dispatch('arena-feedback', type: 'wrong');

            if ((bool) config('services.telegram.notify_wrong', false)) {
                SendTelegramNotification::dispatch(
                    '[WRONG] ' . $player->username . ' x ' . ($this->currentChallenge['engine'] ?? 'unknown')
                    . " | Level {$this->playerLevel}"
                )->onQueue('telegram');
            }

            $this->ambilChallengeBaru();

            return;
        }

        $this->correctCount++;

        $player->total_score += $scoreEarned;
        $player->challenges_completed += 1;

        if ($this->correctCount >= 3) {
            $player->current_level += 1;
            $player->total_score += 500;
            $this->correctCount = 0;
            $this->playerLevel = $player->current_level;
            $this->state = 'map';
            $this->statusMessage = 'Hebat! Kamu naik level.';

            $this->dispatch('level-up-animation', level: $this->playerLevel);
            $this->dispatch('map-focus-active');
        } else {
            $this->dispatch('arena-feedback', type: 'correct');
        }

        $player->last_active_at = now();
        $player->save();
        $this->playerScore = $player->total_score;

        SendTelegramNotification::dispatch(
            '[OK] ' . $player->username . ' | ' . ($this->currentChallenge['engine'] ?? 'unknown')
            . " | +{$scoreEarned} | Progress {$this->correctCount}/3"
        )->onQueue('telegram');

        if ($player->current_level > $oldLevel) {
            event(new PlayerLeveledUpEvent($player));

            SendTelegramNotification::dispatch(
                "[LEVEL UP] {$player->username} -> Level {$player->current_level}! Total: {$player->total_score} poin."
            )->onQueue('telegram');
        }

        if ($this->state === 'arena') {
            $this->ambilChallengeBaru();
        }
    }

    private function hitungSkor(int $timeSpentMs): int
    {
        if ($timeSpentMs < 5000) {
            return 150;
        }

        if ($timeSpentMs < 10000) {
            return 125;
        }

        return 100;
    }

    private function ensureLevelQuestions(int $level): void
    {
        $count = Question::query()->where('level', $level)->count();

        if ($count >= 15) {
            return;
        }

        $assets = config('svg-assets.assets', ['apel', 'kucing', 'balon']);

        for ($i = $count; $i < 15; $i++) {
            $enginePool = ['tap_collector', 'macro_dnd', 'binary_choice'];
            $engine = $enginePool[$i % count($enginePool)];
            $payload = ChallengePayloadFactory::make($level, $engine, $assets);

            Question::query()->create([
                'level' => $level,
                'tipe_engine' => $engine,
                'payload' => $payload,
                'difficulty' => 1,
            ]);
        }
    }

    private function player(): ?Player
    {
        $playerId = session('player_id');

        if (! $playerId) {
            return null;
        }

        return Player::query()->find($playerId);
    }
}
