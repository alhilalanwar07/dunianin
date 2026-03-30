<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DashScopeService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.dashscope.api_key', '');
        $this->baseUrl = rtrim((string) config('services.dashscope.base_url', 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1'), '/');
        $this->model = (string) config('services.dashscope.model', 'wanx2.1-t2i-turbo');
    }

    public function generateImage(string $prompt, string $size = '1024*1024', int $steps = 25): ?string
    {
        if ($this->apiKey === '' || str_contains($this->apiKey, 'your-dashscope-api-key')) {
            return null;
        }

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(120)
            ->post($this->baseUrl . '/text2image', [
                'model' => $this->model,
                'prompt' => $prompt,
                'size' => $size,
                'steps' => $steps,
                'n' => 1,
            ]);

        if (! $response->ok()) {
            return null;
        }

        $data = $response->json();
        $url = $data['data'][0]['url'] ?? null;

        return $url;
    }

    public function generateImageForAsset(string $assetName, string $style = 'cartoon for children illustration'): ?string
    {
        $prompt = "A cute, simple cartoon {$assetName} for young children, bright colors, clean lines, white background, {$style}";
        return $this->generateImage($prompt);
    }
}
