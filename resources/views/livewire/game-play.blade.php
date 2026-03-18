<div
    class="game-area flex h-full w-full flex-col bg-amber-50"
    x-data="{
        showLevelUp: false,
        levelUpText: '',
        livePlayerLevel: $wire.entangle('playerLevel').live,
        livePlayerScore: $wire.entangle('playerScore').live,
        liveCorrectCount: $wire.entangle('correctCount').live
    }"
    x-on:level-up-animation.window="
        levelUpText = `LEVEL ${$event.detail.level}!`;
        showLevelUp = true;
        setTimeout(() => showLevelUp = false, 1800);
    "
    x-on:map-focus-active.window="$nextTick(() => { if ($refs.activeNode) { $refs.activeNode.scrollIntoView({ behavior: 'smooth', block: 'center' }); } })"
>
    <header class="flex items-center justify-between bg-orange-50 px-6 py-4 shadow-sm">
        <div>
            <h1 class="text-3xl font-extrabold text-amber-900">Dunia Anin</h1>
            <p class="text-xl font-bold text-orange-700">Halo, {{ $player?->username ?? 'Pemain' }}</p>
        </div>
        <div class="text-right">
            <p class="text-lg font-bold text-orange-700" x-text="`Level ${livePlayerLevel}`"></p>
            <p class="text-lg font-bold text-amber-900" x-text="`Skor ${new Intl.NumberFormat('id-ID').format(livePlayerScore)}`"></p>
            <a href="{{ route('leaderboard') }}" wire:navigate class="mt-1 inline-block text-lg font-bold text-orange-600 underline transition hover:text-orange-800">
                Leaderboard
            </a>
        </div>
    </header>

    @if ($statusMessage)
        <div class="mx-6 mt-3 rounded-2xl bg-emerald-100 px-4 py-2 text-lg font-bold text-emerald-700">
            {{ $statusMessage }}
        </div>
    @endif

    @if ($state === 'map')
        <section wire:key="view-map" wire:transition.opacity.duration.300ms class="flex-1 overflow-y-auto p-6" x-ref="mapScroller">
            <div class="mx-auto flex max-w-3xl flex-col gap-6 pb-10">
                @for ($level = 1; $level <= $playerLevel + 1; $level++)
                    @php
                        $isCompleted = $level < $playerLevel;
                        $isActive = $level === $playerLevel;
                        $isLocked = $level > $playerLevel;
                        $align = $level % 2 === 0 ? 'self-end' : 'self-start';
                        $connectorDone = $level < $playerLevel;
                    @endphp

                    <div class="{{ $align }} w-64" style="margin-inline: {{ $level % 2 === 0 ? '2.5rem 0' : '0 2.5rem' }};">
                        <button
                            wire:click="masukArena({{ $level }})"
                            wire:loading.class="opacity-60 scale-95"
                            wire:target="masukArena({{ $level }})"
                            @if ($isActive) x-ref="activeNode" @endif
                            class="touch-target relative flex w-full items-center justify-between overflow-hidden rounded-3xl px-5 py-4 text-left shadow-lg transition
                                {{ $isCompleted ? 'bg-emerald-300 text-emerald-900' : '' }}
                                {{ $isActive ? 'animate-pulse-glow bg-amber-400 text-amber-900 hover:scale-105' : '' }}
                                {{ $isLocked ? 'bg-gray-300 text-gray-600 pointer-events-none' : '' }}"
                        >
                            <span class="text-2xl font-extrabold">Level {{ $level }}</span>
                            <span>
                                @if ($isCompleted)
                                    <x-svg-icon name="centang" class="h-10 w-10 text-emerald-700" />
                                @elseif ($isActive)
                                    <x-svg-icon name="mahkota" class="h-10 w-10 text-orange-500" />
                                @else
                                    <x-svg-icon name="gembok" class="h-10 w-10 text-gray-500" />
                                @endif
                            </span>
                        </button>

                        @if ($level < ($playerLevel + 1))
                            <div class="mx-auto my-2 h-14 w-2 rounded-full {{ $connectorDone ? 'bg-emerald-300' : 'bg-gray-300' }}"></div>
                        @endif
                    </div>
                @endfor
            </div>
        </section>
    @endif

    @if ($state === 'arena')
        <section wire:key="view-arena" wire:transition.scale.duration.300ms class="flex-1 p-5">
            @if ($currentChallenge)
                <div
                    wire:key="arena-{{ $currentChallenge['instance_key'] ?? $currentChallenge['question_id'] }}"
                    x-data="gameArena(@js($currentChallenge))"
                    x-init="start($wire)"
                    class="relative flex h-full flex-col overflow-hidden rounded-3xl bg-orange-50 p-5 shadow-lg transition"
                    :class="{
                        'animate-fade-in': visualState === 'playing',
                        'ring-4 ring-emerald-300': visualState === 'correct',
                        'animate-shake ring-4 ring-rose-300': visualState === 'wrong'
                    }"
                >
                    <!-- Loading Overlay -->
                    <div wire:loading.flex wire:target="challengeSelesai, kembaliKeMap" class="absolute inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-sm">
                        <div class="animate-bounce">
                            <x-svg-icon name="mahkota" class="mx-auto h-20 w-20 text-orange-500" />
                            <p class="mt-4 font-bold text-orange-600">Memuat...</p>
                        </div>
                    </div>

                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-3xl font-extrabold text-amber-900" x-text="challenge.prompt"></p>
                        <p class="text-xl font-bold text-orange-700" x-text="`Progress ${liveCorrectCount}/3`"></p>
                    </div>

                    <template x-if="challenge.engine === 'tap_collector'">
                        <div class="grid flex-1 grid-cols-5 gap-4">
                            @for ($i = 0; $i < ($currentChallenge['payload']['spawn_count'] ?? 3); $i++)
                                <button
                                    class="touch-target flex items-center justify-center rounded-3xl bg-white shadow transition"
                                    @click="tap({{ $i }})"
                                    :disabled="items[{{ $i }}]?.done"
                                    :class="items[{{ $i }}]?.done ? 'opacity-30 scale-90' : 'hover:scale-105'"
                                >
                                    <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-16 w-16 text-sky-500" />
                                </button>
                            @endfor
                        </div>
                    </template>

                    <template x-if="challenge.engine === 'macro_dnd'">
                        <div class="flex flex-1 gap-5">
                            <div class="grid w-3/5 grid-cols-3 gap-4 rounded-3xl bg-white p-4">
                                @for ($i = 0; $i < ($currentChallenge['payload']['spawn_count'] ?? 3); $i++)
                                    <div
                                        class="touch-target flex items-center justify-center rounded-2xl bg-orange-100"
                                        draggable="true"
                                        @dragstart="drag({{ $i }})"
                                        x-show="!items[{{ $i }}]?.done"
                                    >
                                        <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-16 w-16 text-violet-500" />
                                    </div>
                                @endfor
                            </div>

                            <div
                                class="flex w-2/5 items-center justify-center rounded-3xl border-4 border-dashed border-amber-400 bg-amber-100"
                                @dragover.prevent
                                @drop.prevent="drop()"
                            >
                                <div class="text-center">
                                    <x-svg-icon name="keranjang" class="mx-auto h-24 w-24 text-orange-500" />
                                    <p class="mt-3 text-2xl font-extrabold text-amber-900">Taruh di sini</p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="challenge.engine === 'binary_choice'">
                        <div class="flex flex-1 gap-4">
                            <button class="flex-1 rounded-3xl bg-white p-4 shadow transition" @click="choose('left')" :class="wrongChoice === 'left' ? 'animate-shake ring-4 ring-rose-300' : ''">
                                <p class="mb-3 text-xl font-bold text-orange-700">KIRI</p>
                                <div class="grid grid-cols-4 gap-2">
                                    @for ($i = 0; $i < ($currentChallenge['payload']['left_count'] ?? 2); $i++)
                                        <div class="flex items-center justify-center rounded-xl bg-orange-100 p-2">
                                            <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-10 w-10 text-rose-500" />
                                        </div>
                                    @endfor
                                </div>
                            </button>

                            <button class="flex-1 rounded-3xl bg-white p-4 shadow transition" @click="choose('right')" :class="wrongChoice === 'right' ? 'animate-shake ring-4 ring-rose-300' : ''">
                                <p class="mb-3 text-xl font-bold text-orange-700">KANAN</p>
                                <div class="grid grid-cols-4 gap-2">
                                    @for ($i = 0; $i < ($currentChallenge['payload']['right_count'] ?? 3); $i++)
                                        <div class="flex items-center justify-center rounded-xl bg-orange-100 p-2">
                                            <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-10 w-10 text-emerald-500" />
                                        </div>
                                    @endfor
                                </div>
                            </button>
                        </div>
                    </template>

                    <div class="mt-4">
                        <button wire:click="kembaliKeMap" class="touch-target rounded-2xl bg-gray-300 px-4 py-2 text-lg font-bold text-gray-700">
                            Kembali ke Peta
                        </button>
                    </div>
                </div>
            @else
                <div class="flex h-full items-center justify-center rounded-3xl bg-orange-50 shadow-lg">
                    <p class="text-2xl font-bold text-orange-700">Memuat tantangan...</p>
                </div>
            @endif
        </section>
    @endif

    <div x-show="showLevelUp" x-transition.opacity class="level-up-overlay">
        <div class="level-up-badge animate-star-burst" x-text="levelUpText"></div>
    </div>

    <script>
        function gameArena(challenge) {
            return {
                challenge,
                items: [],
                draggedIndex: null,
                startedAt: Date.now(),
                wire: null,
                busy: false,
                visualState: 'playing',
                wrongChoice: null,

                start(wire) {
                    this.wire = wire;
                    this.startedAt = Date.now();

                    if (this.challenge.engine === 'tap_collector' || this.challenge.engine === 'macro_dnd') {
                        const total = this.challenge.payload.spawn_count || 3;
                        this.items = Array.from({ length: total }, () => ({ done: false }));
                    }
                },

                elapsed() {
                    return Math.max(1, Date.now() - this.startedAt);
                },

                finish(isCorrect) {
                    if (this.busy) {
                        return;
                    }

                    this.busy = true;

                    if (isCorrect) {
                        this.visualState = 'correct';
                        setTimeout(() => {
                            this.wire.challengeSelesai(this.challenge.question_id, true, this.elapsed());
                        }, 450);

                        return;
                    }

                    this.visualState = 'wrong';
                    setTimeout(() => {
                        this.wire.challengeSelesai(this.challenge.question_id, false, this.elapsed());
                    }, 650);
                },

                tap(index) {
                    if (this.busy || this.items[index].done) {
                        return;
                    }

                    this.items[index].done = true;

                    if (this.items.every(item => item.done)) {
                        this.finish(true);
                    }
                },

                drag(index) {
                    this.draggedIndex = index;
                },

                drop() {
                    if (this.busy || this.draggedIndex === null) {
                        return;
                    }

                    this.items[this.draggedIndex].done = true;
                    this.draggedIndex = null;

                    if (this.items.every(item => item.done)) {
                        this.finish(true);
                    }
                },

                choose(side) {
                    if (this.busy) {
                        return;
                    }

                    const isCorrect = side === this.challenge.payload.answer_side;

                     this.wrongChoice = isCorrect ? null : side;
                    this.finish(isCorrect);
                },
            };
        }
    </script>
</div>
