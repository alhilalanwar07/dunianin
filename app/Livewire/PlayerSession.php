<?php

namespace App\Livewire;

use App\Jobs\SendTelegramNotification;
use App\Models\Player;
use Illuminate\Support\Str;
use Livewire\Component;

class PlayerSession extends Component
{
    public string $username = '';

    public bool $isCheckingSession = true;

    protected function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:2', 'max:50', 'unique:players,username'],
        ];
    }

    public function render()
    {
        return view('livewire.player-session')
            ->layout('layouts.app', ['title' => 'Dunia Anin']);
    }

    public function finishChecking(): void
    {
        $this->isCheckingSession = false;
    }

    public function resumeWithToken(string $token): void
    {
        $player = Player::query()->where('session_token', $token)->first();

        if (! $player) {
            $this->isCheckingSession = false;
            $this->dispatch('session-invalid');

            return;
        }

        $player->forceFill(['last_active_at' => now()])->save();

        session([
            'player_id' => $player->id,
            'session_token' => $player->session_token,
        ]);

        $this->redirectRoute('game.play', navigate: true);
    }

    public function register(): void
    {
        $validated = $this->validate();

        $sessionToken = (string) Str::uuid();

        $player = Player::query()->create([
            'username' => $validated['username'],
            'session_token' => $sessionToken,
            'current_level' => 1,
            'total_score' => 0,
            'challenges_completed' => 0,
            'last_active_at' => now(),
        ]);

        session([
            'player_id' => $player->id,
            'session_token' => $sessionToken,
        ]);

        $this->dispatch('session-created', token: $sessionToken);

        SendTelegramNotification::dispatch(
            "[NEW] \"{$player->username}\" bergabung. Level {$player->current_level}."
        )->onQueue('telegram');

        $this->redirectRoute('game.play', navigate: true);
    }

    public function updatedUsername(): void
    {
        $this->validateOnly('username');
    }
}
