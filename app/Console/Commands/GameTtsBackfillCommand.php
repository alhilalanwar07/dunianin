<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\TtsService;
use Illuminate\Console\Command;

class GameTtsBackfillCommand extends Command
{
    protected $signature = 'game:tts-backfill
        {--level= : Backfill hanya untuk level tertentu}
        {--chunk=100 : Ukuran chunk query}
        {--verify-files : Regenerate jika audio_url ada tapi file MP3 tidak ditemukan}
        {--force : Regenerate walau audio_url sudah ada}';

    protected $description = 'Generate / backfill TTS audio_url untuk bank soal yang belum punya audio.';

    public function handle(): int
    {
        $chunkSize = max(10, (int) $this->option('chunk'));
        $levelOption = $this->option('level');
        $force = (bool) $this->option('force');
        $verifyFiles = (bool) $this->option('verify-files');

        $query = Question::query()->orderBy('id');

        if ($levelOption !== null && $levelOption !== '') {
            $query->where('level', (int) $levelOption);
            $this->info('Mode level: ' . (int) $levelOption);
        } else {
            $this->info('Mode level: semua level');
        }

        $this->info('Chunk size: ' . $chunkSize);
        $this->info('Verify files: ' . ($verifyFiles ? 'ya' : 'tidak'));
        $this->info('Force regenerate: ' . ($force ? 'ya' : 'tidak'));

        $total = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;

        $query->chunkById($chunkSize, function ($questions) use ($force, $verifyFiles, &$total, &$updated, &$skipped, &$failed): void {
            foreach ($questions as $question) {
                $total++;
                $payload = $question->payload;

                $prompt = is_array($payload) ? ($payload['prompt'] ?? null) : null;
                if (! is_string($prompt) || trim($prompt) === '') {
                    $skipped++;
                    continue;
                }

                if (! $force && ! empty($payload['audio_url'])) {
                    if (! $verifyFiles || self::audioFileExists($payload['audio_url'])) {
                        $skipped++;
                        continue;
                    }
                }

                $audioUrl = TtsService::generate($prompt);

                if (! $audioUrl) {
                    $failed++;
                    continue;
                }

                $payload['audio_url'] = $audioUrl;
                $question->update(['payload' => $payload]);
                $updated++;
            }
        });

        $this->newLine();
        $this->info('Backfill selesai.');
        $this->line('Total diproses : ' . $total);
        $this->line('Berhasil update: ' . $updated);
        $this->line('Skip          : ' . $skipped);
        $this->line('Gagal         : ' . $failed);

        return self::SUCCESS;
    }

    private static function audioFileExists(string $audioUrl): bool
    {
        $path = parse_url($audioUrl, PHP_URL_PATH) ?: $audioUrl;
        $absolutePath = public_path(ltrim($path, '/'));

        return is_file($absolutePath);
    }
}
