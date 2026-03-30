<?php

namespace App\Jobs;

use App\Models\Question;
use App\Services\ChallengePayloadFactory;
use App\Services\NimService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateNextLevelsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $level, public int $count = 15)
    {
    }

    public function handle(NimService $nim): void
    {
        $enginePool = ['tap_collector', 'macro_dnd', 'binary_choice', 'match_audio_image', 'memory_pair'];
        $engine = $enginePool[($this->level - 1) % count($enginePool)];

        $rows = $nim->generate($this->level, $engine, $this->count);
        $validPayloads = $this->validatePayloads($rows, $engine, $this->level);

        if ($validPayloads === []) {
            for ($i = 0; $i < $this->count; $i++) {
                $validPayloads[] = ChallengePayloadFactory::make($this->level, $engine);
            }
        }

        foreach ($validPayloads as $payload) {
            Question::query()->create([
                'level' => $this->level,
                'tipe_engine' => $engine,
                'payload' => $payload,
                'difficulty' => 1,
            ]);
        }

        SendTelegramNotification::dispatch(
            "✅ [SYSTEM] {$this->count} soal di-generate untuk Level {$this->level}."
        )->onQueue('telegram');
    }

    private function validatePayloads(array $rows, string $engine, int $level): array
    {
        $maxNumber = 5 + intdiv($level, 2);
        $maxSpawn = min(3 + intdiv($level, 2), 10);
        $assets = config('svg-assets.assets', []);

        $valid = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $asset = $row['target_asset'] ?? null;

            if (! is_string($asset) || ! in_array($asset, $assets, true)) {
                continue;
            }

            if ($engine === 'binary_choice') {
                $left = (int) ($row['left_count'] ?? 0);
                $right = (int) ($row['right_count'] ?? 0);
                $answer = $row['answer_side'] ?? '';

                if ($left < 1 || $right < 1 || $left > $maxNumber || $right > $maxNumber) {
                    continue;
                }

                if (! in_array($answer, ['left', 'right'], true)) {
                    continue;
                }
            } elseif ($engine === 'match_audio_image') {
                $choices = $row['choices'] ?? [];
                $answerIndex = (int) ($row['answer_index'] ?? -1);

                if (! is_array($choices) || count($choices) !== 4 || ! array_key_exists($answerIndex, $choices)) {
                    continue;
                }

                if (array_filter($choices, fn ($choice) => ! is_string($choice) || ! in_array($choice, $assets, true)) !== []) {
                    continue;
                }
            } elseif ($engine === 'memory_pair') {
                $cards = $row['cards'] ?? [];

                if (! is_array($cards) || count($cards) !== 4) {
                    continue;
                }

                if (array_filter($cards, fn ($card) => ! is_string($card) || ! in_array($card, $assets, true)) !== []) {
                    continue;
                }

                if (count(array_keys($cards, $asset, true)) !== 2) {
                    continue;
                }
            } else {
                $spawn = (int) ($row['spawn_count'] ?? 0);

                if ($spawn < 2 || $spawn > $maxSpawn) {
                    continue;
                }
            }

            $valid[] = [
                'prompt' => (string) ($row['prompt'] ?? 'Mainkan tantangan ini.'),
                'target_asset' => $asset,
                'spawn_count' => (int) ($row['spawn_count'] ?? 0),
                'left_count' => (int) ($row['left_count'] ?? 0),
                'right_count' => (int) ($row['right_count'] ?? 0),
                'answer_side' => (string) ($row['answer_side'] ?? ''),
                'choices' => array_values(array_filter($row['choices'] ?? [], 'is_string')),
                'answer_index' => (int) ($row['answer_index'] ?? 0),
                'cards' => array_values(array_filter($row['cards'] ?? [], 'is_string')),
            ];
        }

        if (count($valid) < max(1, intdiv($this->count, 3))) {
            $this->writeValidationLog($level, $engine, $rows);
        }

        return array_slice($valid, 0, $this->count);
    }

    private function writeValidationLog(int $level, string $engine, array $rows): void
    {
        $line = '[' . now()->toDateTimeString() . "] Invalid NIM payload | level={$level} | engine={$engine} | raw=" . json_encode($rows) . PHP_EOL;
        $path = storage_path('logs/nim-validation.log');
        file_put_contents($path, $line, FILE_APPEND);
    }
}
