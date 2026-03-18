<div class="game-area flex h-full w-full items-center justify-center bg-amber-50 p-6">
    <div class="w-full max-w-4xl rounded-3xl bg-orange-50 p-6 shadow-xl">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-4xl font-extrabold text-amber-900">Leaderboard</h1>
            <a href="{{ route('game.play') }}" class="touch-target rounded-2xl bg-amber-400 px-4 py-2 text-xl font-bold text-amber-900">
                Kembali
            </a>
        </div>

        <div class="space-y-3">
            @foreach ($players as $index => $row)
                <div class="flex items-center justify-between rounded-2xl px-4 py-3 {{ $row->id === $currentPlayerId ? 'bg-amber-200' : 'bg-white' }}">
                    <div class="flex items-center gap-3">
                        <span class="w-10 text-2xl font-extrabold text-orange-700">{{ $index + 1 }}</span>
                        <p class="text-2xl font-bold text-amber-900">{{ $row->username }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-orange-700">Level {{ $row->current_level }}</p>
                        <p class="text-xl font-bold text-amber-900">{{ number_format($row->total_score) }} poin</p>
                    </div>
                </div>
            @endforeach

            @if ($players->isEmpty())
                <p class="rounded-2xl bg-white px-4 py-6 text-center text-xl font-bold text-orange-700">Belum ada data pemain.</p>
            @endif
        </div>
    </div>
</div>
