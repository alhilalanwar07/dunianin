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
    <header class="flex flex-col items-center justify-between gap-4 bg-orange-50 px-6 py-4 shadow-sm md:flex-row">
        <div class="text-center md:text-left">
            <h1 class="text-3xl font-extrabold text-amber-900">Dunia Anin</h1>
            <p class="text-xl font-bold text-orange-700">Halo, {{ $player?->username ?? 'Pemain' }}</p>
        </div>
        <div class="flex flex-col items-center gap-2 text-center md:items-end md:text-right">
            <div class="flex gap-4">
                <p class="text-lg font-bold text-orange-700" x-text="`Level ${livePlayerLevel}`"></p>
                <p class="text-lg font-bold text-amber-900" x-text="`Skor ${new Intl.NumberFormat('id-ID').format(livePlayerScore)}`"></p>
            </div>
            <a href="{{ route('leaderboard') }}" wire:navigate class="inline-block text-lg font-bold text-orange-600 underline transition hover:text-orange-800">
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

                    <!-- Success Overlay -->
                    <div x-show="visualState === 'success_dialog'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" class="absolute inset-0 z-40 flex flex-col items-center justify-center bg-emerald-500/95 backdrop-blur-sm">
                        <div class="mb-6 animate-bounce">
                            <x-svg-icon name="centang" class="h-32 w-32 text-white drop-shadow-md" />
                        </div>
                        <h2 class="mb-8 text-center text-4xl font-extrabold text-white drop-shadow-md">Hebat!<br>Jawabanmu Benar</h2>
                        <button type="button" @click="if(!busy) { busy = true; $wire.challengeSelesai(challenge.question_id, true, elapsed()); }" class="touch-target rounded-full bg-white px-8 py-4 text-2xl font-extrabold text-emerald-600 shadow-xl transition hover:scale-105 active:scale-95 outline-none pointer-events-auto">
                            Lanjut
                        </button>
                    </div>

                    <div class="mb-4 flex flex-col items-center justify-between gap-2 md:flex-row md:gap-0">
                        <p class="text-center text-3xl font-extrabold text-amber-900 md:text-left" x-text="challenge.prompt"></p>
                        <p class="rounded-full bg-orange-200 px-4 py-1 text-xl font-bold text-orange-700" x-text="`Progress ${liveCorrectCount}/3`"></p>
                    </div>

                    <template x-if="challenge.engine === 'tap_collector'">
                        <div class="flex flex-1 flex-wrap items-center justify-center gap-6 p-4 perspective-1000">
                            @for ($i = 0; $i < ($currentChallenge['payload']['spawn_count'] ?? 3); $i++)
                                <button
                                    class="touch-target relative flex h-32 w-32 items-center justify-center rounded-full bg-gradient-to-br from-sky-300 to-indigo-400 shadow-[0_10px_20px_rgba(0,0,0,0.2)] transition-all duration-300"
                                    @click="tap({{ $i }})"
                                    :disabled="items[{{ $i }}]?.done"
                                    :class="items[{{ $i }}]?.done 
                                        ? 'opacity-0 scale-50 -translate-y-20 rotate-180 pointer-events-none' 
                                        : 'hover:scale-110 hover:-rotate-12 active:scale-95 animate-pulse-glow'"
                                    style="animation-delay: {{ $i * 200 }}ms; transform-style: preserve-3d;"
                                >
                                    <div class="pointer-events-none absolute inset-0 rounded-full border-4 border-white/40"></div>
                                    <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-20 w-20 text-white drop-shadow-xl transition-transform" x-bind:class="items[{{ $i }}]?.done ? '' : 'animate-bounce'" style="animation-delay: {{ $i * 150 }}ms" />
                                </button>
                            @endfor
                        </div>
                    </template>

                    <template x-if="challenge.engine === 'macro_dnd'">
                        <div class="flex flex-1 flex-col gap-6 md:flex-row p-2">
                            <div class="flex w-full md:w-3/5 flex-wrap items-center justify-center gap-6 rounded-[2rem] bg-white/50 p-6 shadow-inner" style="min-height: 280px;">
                                @for ($i = 0; $i < ($currentChallenge['payload']['spawn_count'] ?? 3); $i++)
                                    <div
                                        class="touch-target flex h-28 w-28 cursor-grab items-center justify-center rounded-3xl bg-gradient-to-br from-violet-400 to-fuchsia-400 shadow-xl transition-all duration-300 hover:scale-110 hover:-rotate-6 active:cursor-grabbing active:scale-95"
                                        draggable="true"
                                        @dragstart="drag({{ $i }})"
                                        x-show="!items[{{ $i }}]?.done"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 scale-50 translate-y-10"
                                    >
                                        <div class="pointer-events-none absolute inset-0 rounded-3xl border-4 border-white/30"></div>
                                        <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-20 w-20 text-white drop-shadow-lg" />
                                    </div>
                                @endfor
                            </div>

                            <div
                                class="flex w-full md:w-2/5 items-center justify-center rounded-[2rem] border-8 border-dashed border-amber-400 bg-amber-100/50 p-6 shadow-lg transition-all duration-300"
                                :class="draggedIndex !== null ? 'scale-[1.02] border-amber-500 bg-amber-200/80 shadow-2xl animate-pulse' : ''"
                                @dragover.prevent
                                @drop.prevent="drop()"
                            >
                                <div class="text-center transition-transform duration-300" :class="draggedIndex !== null ? 'scale-110' : ''">
                                    <div class="mx-auto flex h-40 w-40 items-center justify-center rounded-full bg-white/50 shadow-inner mb-4">
                                        <x-svg-icon name="keranjang" class="h-28 w-28 text-orange-500 drop-shadow-md" />
                                    </div>
                                    <p class="text-3xl font-extrabold text-amber-900 drop-shadow-sm" x-text="draggedIndex !== null ? 'Lepaskan!' : 'Taruh di sini'"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="challenge.engine === 'binary_choice'">
                        <div class="flex flex-1 flex-col gap-6 md:flex-row p-2">
                            <button class="group flex-1 rounded-[2.5rem] bg-gradient-to-b from-rose-100 to-rose-200 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.12)] transition-all duration-500 hover:-translate-y-4 hover:shadow-rose-400/50 active:scale-95" 
                                @click="choose('left')" 
                                :class="wrongChoice === 'left' ? 'animate-shake ring-8 ring-rose-400 bg-rose-300' : ''">
                                <p class="mb-6 inline-block rounded-full bg-rose-500 px-8 py-3 text-2xl font-black tracking-widest text-white shadow-lg">KIRI</p>
                                <div class="flex flex-wrap items-center justify-center gap-4">
                                    @for ($i = 0; $i < ($currentChallenge['payload']['left_count'] ?? 2); $i++)
                                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white shadow-md transition-transform duration-300 group-hover:rotate-12 group-hover:scale-110">
                                            <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-12 w-12 text-rose-500 drop-shadow-sm" />
                                        </div>
                                    @endfor
                                </div>
                            </button>

                            <button class="group flex-1 rounded-[2.5rem] bg-gradient-to-b from-emerald-100 to-emerald-200 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.12)] transition-all duration-500 hover:-translate-y-4 hover:shadow-emerald-400/50 active:scale-95" 
                                @click="choose('right')" 
                                :class="wrongChoice === 'right' ? 'animate-shake ring-8 ring-rose-400 bg-rose-300' : ''">
                                <p class="mb-6 inline-block rounded-full bg-emerald-500 px-8 py-3 text-2xl font-black tracking-widest text-white shadow-lg">KANAN</p>
                                <div class="flex flex-wrap items-center justify-center gap-4">
                                    @for ($i = 0; $i < ($currentChallenge['payload']['right_count'] ?? 3); $i++)
                                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white shadow-md transition-transform duration-300 group-hover:-rotate-12 group-hover:scale-110">
                                            <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-12 w-12 text-emerald-500 drop-shadow-sm" />
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
                finalTime: null,
                wire: null,
                busy: false,
                visualState: 'playing',
                wrongChoice: null,

                start(wire) {
                    this.wire = wire;
                    this.startedAt = Date.now();
                    this.finalTime = null;

                    if (this.challenge.engine === 'tap_collector' || this.challenge.engine === 'macro_dnd') {
                        const total = this.challenge.payload.spawn_count || 3;
                        this.items = Array.from({ length: total }, () => ({ done: false }));
                    }
                },

                elapsed() {
                    if (this.finalTime !== null) {
                        return this.finalTime;
                    }
                    return Math.max(1, Date.now() - this.startedAt);
                },

                finish(isCorrect) {
                    if (this.busy) {
                        return;
                    }

                    this.busy = true;
                    this.finalTime = this.elapsed();

                    if (isCorrect) {
                        this.visualState = 'correct';
                        setTimeout(() => {
                            this.visualState = 'success_dialog';
                            this.busy = false;
                        }, 450);

                        return;
                    }

                    this.visualState = 'wrong';
                    setTimeout(async () => {
                        try {
                            await this.wire.challengeSelesai(this.challenge.question_id, false, this.elapsed());
                        } catch (e) {
                            this.busy = false;
                        }
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
