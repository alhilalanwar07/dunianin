<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_status_command_runs_successfully(): void
    {
        Player::query()->create([
            'username' => 'Nina',
            'session_token' => '44444444-4444-4444-4444-444444444444',
            'current_level' => 3,
            'total_score' => 2000,
            'challenges_completed' => 12,
            'last_active_at' => now(),
        ]);

        Question::query()->create([
            'level' => 1,
            'tipe_engine' => 'tap_collector',
            'payload' => [
                'prompt' => 'Ketuk semua apel!',
                'target_asset' => 'apel',
                'spawn_count' => 3,
            ],
            'difficulty' => 1,
        ]);

        $this->artisan('game:status')
            ->expectsOutputToContain('Dunia Anin Status')
            ->expectsOutputToContain('Total pemain')
            ->assertSuccessful();
    }
}
