<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class NimService
{
    public function generate(int $level, string $engine, int $count = 15): array
    {
        $apiKey = (string) config('services.nvidia_nim.api_key');

        if ($apiKey === '' || str_contains($apiKey, 'your-nvidia-nim-api-key')) {
            return $this->fallback($level, $engine, $count);
        }

        $maxNumber = 5 + intdiv($level, 2);
        $maxSpawn = min(3 + intdiv($level, 2), 10);
        $assets = implode(', ', config('svg-assets.assets', []));

        $prompt = $this->buildPrompt($engine, $count, $maxNumber, $maxSpawn, $assets);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(60)
            ->post(rtrim((string) config('services.nvidia_nim.base_url'), '/') . '/chat/completions', [
                'model' => config('services.nvidia_nim.model', 'meta/llama-3.1-70b-instruct'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You generate strict JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.4,
                'max_tokens' => 3000,
            ]);

        if (! $response->ok()) {
            return $this->fallback($level, $engine, $count);
        }

        $content = Arr::get($response->json(), 'choices.0.message.content', '[]');
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            return $this->fallback($level, $engine, $count);
        }

        return $decoded;
    }

    private function fallback(int $level, string $engine, int $count): array
    {
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $rows[] = ChallengePayloadFactory::make($level, $engine);
        }

        return $rows;
    }

    private function buildPrompt(string $engine, int $count, int $maxNumber, int $maxSpawn, string $assets): string
    {
        return <<<PROMPT
Kamu adalah desainer soal game edukasi untuk anak usia 5-7 tahun.
TUGAS: Hasilkan tepat {$count} objek JSON untuk engine "{$engine}".

BATASAN KETAT:
1. Angka maksimum di layar: {$maxNumber}
2. Jumlah objek spawn: antara 2 dan {$maxSpawn}
3. HANYA gunakan aset dari: {$assets}
4. Output HANYA JSON array. TANPA teks lain. TANPA markdown.
5. Masing-masing item punya prompt dan field sesuai engine.
PROMPT;
    }
}
