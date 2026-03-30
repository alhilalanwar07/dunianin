<div class="game-area flex h-full w-full items-center justify-center bg-amber-50 p-3 md:p-6" wire:poll.2s.visible>
    <div class="w-full max-w-4xl rounded-3xl bg-orange-50 p-4 shadow-xl md:p-6">
        <div class="mb-4 flex items-start justify-between gap-3 md:items-center">
            <div>
                <h1 class="text-2xl font-extrabold text-amber-900 md:text-4xl">Leaderboard</h1>
                <p class="text-xs font-bold text-orange-600 md:text-sm">
                    Realtime update tiap {{ $this->refreshSeconds }} detik · terakhir {{ $updatedAt }}
                </p>
            </div>
            <a href="{{ route('game.play') }}" class="touch-target rounded-2xl bg-amber-400 px-3 py-2 text-sm font-bold text-amber-900 md:px-4 md:text-xl">
                Kembali
            </a>
        </div>

        <div class="space-y-2 md:space-y-3">
            @foreach ($players as $index => $row)
                <div
                    wire:key="leaderboard-row-{{ $row->id }}-{{ $row->total_score }}-{{ $row->current_level }}"
                    class="flex items-center justify-between gap-3 rounded-2xl px-3 py-2.5 transition md:px-4 md:py-3 {{ $row->id === $currentPlayerId ? 'bg-amber-200 ring-2 ring-amber-400' : 'bg-white' }}"
                >
                    <div class="flex min-w-0 items-center gap-2 md:gap-3">
                        <span class="w-7 text-lg font-extrabold text-orange-700 md:w-10 md:text-2xl">{{ $index + 1 }}</span>
                        <p class="truncate text-base font-bold text-amber-900 md:text-2xl">{{ $row->username }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-orange-700 md:text-xl">Level {{ $row->current_level }}</p>
                        <p class="text-sm font-bold text-amber-900 md:text-xl">{{ number_format($row->total_score) }} poin</p>
                    </div>
                </div>
            @endforeach

            @if ($players->isEmpty())
                <p class="rounded-2xl bg-white px-4 py-6 text-center text-base font-bold text-orange-700 md:text-xl">Belum ada data pemain.</p>
            @endif
        </div>
    </div>
</div>
