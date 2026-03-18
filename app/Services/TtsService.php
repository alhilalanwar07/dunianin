<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TtsService
{
    /**
     * Hit undocumented Google Translate TTS API, save to storage, return URL.
     */
    public static function generate(string $text): ?string
    {
        $filename = 'voices/' . md5($text) . '.mp3';

        // Jika sudah pernah di-generate, return cache URL-nya
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
                Storage::disk('public')->put($filename, $response->body());
                return Storage::url($filename);
            }

            Log::error('Google TTS Failed: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('Google TTS Error: ' . $e->getMessage());
        }

        return null;
    }
}