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
            'match_audio_image' => self::makeMatchAudioImage($assetPool),
            'memory_pair' => self::makeMemoryPair($assetPool),
            default => self::makeBinaryChoice($assetPool, $maxSpawn),
        };

        // Generate TTS Audio silently for the prompt
        if (isset($payload['prompt'])) {
            $payload['audio_url'] = TtsService::generate($payload['prompt']);
        }

        if (isset($payload['target_asset'])) {
            $payload['image_url'] = (new DashScopeService())->generateImageForAsset($payload['target_asset']);
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

    private static function makeMatchAudioImage(array $assetPool): array
    {
        $targetAsset = $assetPool[array_rand($assetPool)];
        $distractors = $assetPool;
        unset($distractors[array_search($targetAsset, $distractors)]);
        $distractors = array_values($distractors);
        $correctIndex = random_int(0, 3);

        $choices = array_fill(0, 4, null);
        $choices[$correctIndex] = $targetAsset;

        for ($i = 0; $i < 4; $i++) {
            if ($choices[$i] === null) {
                $choices[$i] = $distractors[array_rand($distractors)];
            }
        }

        return [
            'prompt' => 'Klik gambar ' . $targetAsset . '!',
            'target_asset' => $targetAsset,
            'choices' => $choices,
            'answer_index' => $correctIndex,
        ];
    }

    private static function makeMemoryPair(array $assetPool): array
    {
        $targetAsset = $assetPool[array_rand($assetPool)];
        $distractors = array_values(array_filter($assetPool, fn ($asset) => $asset !== $targetAsset));
        shuffle($distractors);

        $cards = [
            $targetAsset,
            $targetAsset,
            $distractors[0] ?? $targetAsset,
            $distractors[1] ?? $targetAsset,
        ];

        shuffle($cards);

        return [
            'prompt' => 'Temukan dua gambar ' . $targetAsset . ' yang sama!',
            'target_asset' => $targetAsset,
            'cards' => $cards,
        ];
    }
}
