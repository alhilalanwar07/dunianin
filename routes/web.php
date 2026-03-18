<?php

use App\Livewire\GamePlay;
use App\Livewire\Leaderboard;
use App\Livewire\PlayerSession;
use Illuminate\Support\Facades\Route;

Route::get('/', PlayerSession::class)->name('home');
Route::get('/play', GamePlay::class)->name('game.play');
Route::get('/leaderboard', Leaderboard::class)->name('leaderboard');
