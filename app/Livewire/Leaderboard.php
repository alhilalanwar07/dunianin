<?php

namespace App\Livewire;

use App\Models\Player;
use Livewire\Component;

class Leaderboard extends Component
{
    public int $refreshSeconds = 2;

    public function render()
    {
        return view('livewire.leaderboard', [
            'players' => Player::query()
                ->orderByDesc('total_score')
                ->orderByDesc('current_level')
                ->limit(10)
                ->get(),
            'currentPlayerId' => session('player_id'),
            'updatedAt' => now()->format('H:i:s'),
        ])->layout('layouts.app', ['title' => 'Leaderboard - Dunia Anin']);
    }
}
