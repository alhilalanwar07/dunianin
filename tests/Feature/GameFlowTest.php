<?php

namespace Tests\Feature;

use App\Livewire\GamePlay;
use App\Livewire\PlayerSession;
use App\Models\Player;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GameFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_can_register_via_session_component(): void
    {
        Livewire::test(PlayerSession::class)
            ->set('username', 'Anin')
            ->call('register');

        $this->assertDatabaseHas('players', [
            'username' => 'Anin',
            'current_level' => 1,
        ]);
    }

    public function test_player_levels_up_after_three_correct_answers(): void
    {
        $player = Player::query()->create([
            'username' => 'Budi',
            'session_token' => '11111111-1111-1111-1111-111111111111',
            'current_level' => 1,
            'total_score' => 0,
            'challenges_completed' => 0,
            'last_active_at' => now(),
        ]);

        $question = Question::query()->create([
            'level' => 1,
            'tipe_engine' => 'tap_collector',
            'payload' => [
                'prompt' => 'Ketuk semua kucing!',
                'target_asset' => 'kucing',
                'spawn_count' => 3,
            ],
            'difficulty' => 1,
        ]);

        $this->withSession(['player_id' => $player->id]);

        $component = Livewire::test(GamePlay::class);

        $component->set('currentChallenge', [
            'question_id' => $question->id,
            'engine' => 'tap_collector',
            'prompt' => 'Ketuk semua kucing!',
            'payload' => [
                'target_asset' => 'kucing',
                'spawn_count' => 3,
            ],
        ]);

        $component->call('challengeSelesai', $question->id, true, 1200);
        $component->call('challengeSelesai', $question->id, true, 1300);
        $component->call('challengeSelesai', $question->id, true, 1400);

        $player->refresh();

        $this->assertSame(2, $player->current_level);
        $this->assertSame(950, $player->total_score);
        $this->assertSame(3, $player->challenges_completed);
    }

    public function test_leaderboard_page_is_accessible(): void
    {
        Player::query()->create([
            'username' => 'Ayu',
            'session_token' => '22222222-2222-2222-2222-222222222222',
            'current_level' => 2,
            'total_score' => 1200,
            'challenges_completed' => 8,
            'last_active_at' => now(),
        ]);

        $response = $this->get('/leaderboard');

        $response->assertStatus(200);
        $response->assertSee('Leaderboard');
        $response->assertSee('Ayu');
    }

    public function test_wrong_answer_logs_zero_score_and_keeps_level(): void
    {
        $player = Player::query()->create([
            'username' => 'Caca',
            'session_token' => '33333333-3333-3333-3333-333333333333',
            'current_level' => 1,
            'total_score' => 300,
            'challenges_completed' => 2,
            'last_active_at' => now(),
        ]);

        $question = Question::query()->create([
            'level' => 1,
            'tipe_engine' => 'binary_choice',
            'payload' => [
                'prompt' => 'Mana yang lebih banyak?',
                'target_asset' => 'kucing',
                'left_count' => 2,
                'right_count' => 4,
                'answer_side' => 'right',
            ],
            'difficulty' => 1,
        ]);

        $this->withSession(['player_id' => $player->id]);

        $component = Livewire::test(GamePlay::class);

        $component->set('currentChallenge', [
            'question_id' => $question->id,
            'engine' => 'binary_choice',
            'prompt' => 'Mana yang lebih banyak?',
            'payload' => [
                'target_asset' => 'kucing',
                'left_count' => 2,
                'right_count' => 4,
                'answer_side' => 'right',
            ],
        ]);

        $component->call('challengeSelesai', $question->id, false, 2400);

        $player->refresh();

        $this->assertSame(1, $player->current_level);
        $this->assertSame(300, $player->total_score);
        $this->assertDatabaseHas('player_question_logs', [
            'player_id' => $player->id,
            'question_id' => $question->id,
            'is_correct' => 0,
            'score_earned' => 0,
        ]);
    }

    public function test_next_round_gets_new_instance_key_even_with_same_question(): void
    {
        $player = Player::query()->create([
            'username' => 'Dedi',
            'session_token' => '55555555-5555-5555-5555-555555555555',
            'current_level' => 1,
            'total_score' => 0,
            'challenges_completed' => 0,
            'last_active_at' => now(),
        ]);

        $question = Question::query()->create([
            'level' => 1,
            'tipe_engine' => 'tap_collector',
            'payload' => [
                'prompt' => 'Ketuk semua apel!',
                'target_asset' => 'apel',
                'spawn_count' => 3,
            ],
            'difficulty' => 1,
        ]);

        $this->withSession(['player_id' => $player->id]);

        $component = Livewire::test(GamePlay::class);
        $component->call('masukArena', 1);

        $first = $component->get('currentChallenge');
        $this->assertNotNull($first);

        $component->call('challengeSelesai', $question->id, true, 1800);

        $second = $component->get('currentChallenge');
        $this->assertNotNull($second);

        $this->assertNotSame($first['instance_key'], $second['instance_key']);
    }

    public function test_match_audio_image_challenge_renders_without_svg_dependency_error(): void
    {
        $player = Player::query()->create([
            'username' => 'Eka',
            'session_token' => '66666666-6666-6666-6666-666666666666',
            'current_level' => 4,
            'total_score' => 0,
            'challenges_completed' => 0,
            'last_active_at' => now(),
        ]);

        $this->withSession(['player_id' => $player->id]);

        Livewire::test(GamePlay::class)
            ->set('state', 'arena')
            ->set('currentChallenge', [
                'question_id' => 'question-match-audio-image',
                'instance_key' => 'question-match-audio-image-1',
                'engine' => 'match_audio_image',
                'prompt' => 'Klik gambar balon!',
                'payload' => [
                    'target_asset' => 'balon',
                    'choices' => ['balon', 'ayam', 'apel', 'pisang'],
                    'answer_index' => 0,
                ],
            ])
            ->assertSee('Pilih gambar yang cocok dengan suaranya!')
            ->assertSee('balon')
            ->assertSee('ayam');
    }
}
