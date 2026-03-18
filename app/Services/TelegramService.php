<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    public function send(string $message): void
    {
        $token = (string) config('services.telegram.bot_token');
        $chatId = (string) config('services.telegram.chat_id');

        if ($token === '' || $chatId === '' || str_contains($token, 'your-telegram-bot-token')) {
            return;
        }

        Http::timeout(20)->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }
}
