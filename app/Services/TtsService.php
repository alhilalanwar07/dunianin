<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TtsService
{
    private static function publicUrl(string $relativePath): string
    {
        return '/' . ltrim($relativePath, '/');
    }

    /**
     * Hit undocumented Google Translate TTS API, save to storage, return URL.
     */
    public static function generate(string $text): ?string
    {
        $filename = 'voices/' . md5($text) . '.mp3';
        $publicRelativePath = 'storage/' . $filename;
        $publicAbsolutePath = public_path($publicRelativePath);

        // Prioritas: file yang langsung ada di public/storage (cocok untuk hosting tanpa symlink)
        if (is_file($publicAbsolutePath)) {
            return self::publicUrl($publicRelativePath);
        }

        // Fallback: file dari disk public Laravel (cocok jika storage:link tersedia)
        if (Storage::disk('public')->exists($filename)) {
            return Storage::url($filename);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->get('https://translate.google.com/translate_tts', [
                'ie' => 'UTF-8',
                'q' => $text,
                'tl' => 'id', // Bahasa Indonesia
                'client' => 'tw-ob', // Magic client id for free TTS
            ]);

            if ($response->successful()) {
                $audioBinary = $response->body();

                // Simpan langsung ke public/storage agar tidak butuh fungsi symlink() di shared hosting
                $publicDir = dirname($publicAbsolutePath);
                if (! is_dir($publicDir)) {
                    mkdir($publicDir, 0775, true);
                }

                if (@file_put_contents($publicAbsolutePath, $audioBinary) !== false) {
                    return self::publicUrl($publicRelativePath);
                }

                // Fallback terakhir ke disk public Laravel jika direct write gagal
                Storage::disk('public')->put($filename, $audioBinary);

                return Storage::url($filename);
            }

            Log::error('Google TTS Failed: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('Google TTS Error: ' . $e->getMessage());
        }

        return null;
    }
}