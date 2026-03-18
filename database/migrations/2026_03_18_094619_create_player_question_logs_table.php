<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player_question_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignUlid('question_id')->constrained('questions')->onDelete('cascade');
            $table->integer('level_saat_main');
            $table->boolean('is_correct');
            $table->integer('score_earned')->default(0);
            $table->integer('time_spent_ms');
            $table->timestamps();

            $table->index('player_id');
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_question_logs');
    }
};
