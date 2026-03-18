<?php

namespace App\Services;

class ChallengePayloadFactory
{
    public static function make(int $level, string $engine, ?array $assets = null): array
    {
        $assetPool = $assets ?: config('svg-assets.assets', ['apel', 'kucing', 'balon']);
        $maxSpawn = min(3 + intdiv($level, 2), 10);

        $payload = match ($engine) {
            'tap_collector' => [
                'prompt' => 'Ketuk semua ' . $assetPool[array_rand($assetPool)] . '!',
                'target_asset' => $assetPool[array_rand($assetPool)],
                'spawn_count' => random_int(2, max(2, $maxSpawn)),
            ],
            'macro_dnd' => [
                'prompt' => 'Seret semua objek ke keranjang!',
                'target_asset' => $assetPool[array_rand($assetPool)],
                'spawn_count' => random_int(2, max(2, $maxSpawn)),
            ],
            default => self::makeBinaryChoice($assetPool, $maxSpawn),
        };

        // Generate TTS Audio silently for the prompt
        if (isset($payload['prompt'])) {
            $payload['audio_url'] = TtsService::generate($payload['prompt']);
        }

        return $payload;
    }

    private static function makeBinaryChoice(array $assetPool, int $maxSpawn): array
    {
        $leftCount = random_int(2, max(2, $maxSpawn - 1));
        $rightCount = random_int(2, max(2, $maxSpawn));

        if ($leftCount === $rightCount) {
            $rightCount = min(10, $rightCount + 1);
        }

        return [
            'prompt' => 'Mana yang lebih banyak?',
            'target_asset' => $assetPool[array_rand($assetPool)],
            'left_count' => $leftCount,
            'right_count' => $rightCount,
            'answer_side' => $leftCount > $rightCount ? 'left' : 'right',
        ];
    }
}
