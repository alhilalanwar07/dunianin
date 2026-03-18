<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $message)
    {
    }

    public function handle(TelegramService $telegram): void
    {
        $telegram->send($this->message);
    }
}
