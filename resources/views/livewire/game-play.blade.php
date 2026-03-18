<div
    class="game-area relative flex h-full min-h-0 w-full flex-col overflow-hidden bg-amber-50"
    x-data="{
        showLevelUp: false,
        levelUpText: '',
        livePlayerLevel: $wire.entangle('playerLevel').live,
        livePlayerScore: $wire.entangle('playerScore').live,
        liveCorrectCount: $wire.entangle('correctCount').live,
        scrollMapToTop() {
            if (this.$refs.mapScroller) {
                this.$refs.mapScroller.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    }"
    x-on:level-up-animation.window="
        levelUpText = `LEVEL ${$event.detail.level}!`;
        showLevelUp = true;
        setTimeout(() => showLevelUp = false, 1800);
    "
    x-on:map-focus-active.window="$nextTick(() => { if ($refs.activeNode) { $refs.activeNode.scrollIntoView({ behavior: 'smooth', block: 'center' }); } })"
>
    <header class="pointer-events-none absolute inset-x-2 top-2 z-30 rounded-2xl border border-orange-200/80 bg-orange-50/90 px-3 py-2 shadow-md backdrop-blur-sm min-[520px]:inset-x-3 sm:inset-x-4 sm:rounded-3xl sm:px-4 sm:py-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <h1 class="truncate text-base font-extrabold text-amber-900 min-[520px]:text-lg sm:text-2xl">Dunia Anin</h1>
                <p class="truncate text-xs font-bold text-orange-700 min-[520px]:text-sm sm:text-lg">Halo, {{ $player?->username ?? 'Pemain' }}</p>
            </div>
            <div class="text-right">
                <div class="flex items-center justify-end gap-2 min-[520px]:gap-3 sm:gap-4">
                    <p class="text-xs font-bold text-orange-700 min-[520px]:text-sm sm:text-lg" x-text="`Level ${livePlayerLevel}`"></p>
                    <p class="text-xs font-bold text-amber-900 min-[520px]:text-sm sm:text-lg" x-text="`Skor ${new Intl.NumberFormat('id-ID').format(livePlayerScore)}`"></p>
                </div>
                <a href="{{ route('leaderboard') }}" wire:navigate class="pointer-events-auto inline-block text-[11px] font-bold text-orange-700 underline underline-offset-2 transition hover:text-orange-800 min-[520px]:text-xs sm:text-base">
                    Leaderboard
                </a>
            </div>
        </div>
    </header>

    <div class="flex flex-1 flex-col min-h-0 pt-20 min-[520px]:pt-24 sm:pt-28">
        @if ($statusMessage)
            <div class="mx-3 mb-2 shrink-0 rounded-2xl bg-emerald-100 px-3 py-2 text-sm font-bold text-emerald-700 sm:mx-6 sm:mb-3 sm:px-4 sm:text-lg">
                {{ $statusMessage }}
            </div>
        @endif

    @if ($state === 'map')
        <section wire:key="view-map" wire:transition.opacity.duration.300ms class="flex-1 min-h-0 touch-pan-y overflow-y-auto overscroll-y-contain px-2 pb-4 pt-2 [-webkit-overflow-scrolling:touch] min-[520px]:px-3 sm:px-5" x-ref="mapScroller">
            <div class="mx-auto w-full max-w-4xl rounded-3xl border border-orange-100/80 bg-gradient-to-b from-orange-50 via-amber-50 to-orange-100/30 p-3 shadow-[0_14px_34px_rgba(217,119,6,0.14)] sm:rounded-[2rem] sm:p-4">
                <div class="sticky top-0 z-20 mb-2 flex justify-center sm:justify-end">
                    <button type="button" @click="scrollMapToTop()" class="touch-target rounded-full border border-orange-300/80 bg-white/90 px-3 py-1 text-sm font-extrabold text-orange-700 shadow-sm backdrop-blur transition hover:bg-white sm:px-4 sm:py-1.5 sm:text-base">
                        Ke Level 1
                    </button>
                </div>
                <div class="relative mx-auto flex max-w-3xl flex-col gap-2 pb-2 sm:gap-3">
                    <div class="pointer-events-none absolute inset-y-0 left-1/2 w-1 -translate-x-1/2 rounded-full bg-gradient-to-b from-emerald-300 via-amber-300 to-gray-300/70 opacity-40"></div>
                @for ($level = 1; $level <= $playerLevel + 1; $level++)
                    @php
                        $isCompleted = $level < $playerLevel;
                        $isActive = $level === $playerLevel;
                        $isLocked = $level > $playerLevel;
                        $rowAlign = $level % 2 === 0 ? 'justify-end' : 'justify-start';
                        $connectorDone = $level < $playerLevel;
                    @endphp

                    <div class="relative flex w-full {{ $rowAlign }}">
                        <div class="z-10 w-[min(82%,21rem)] min-[520px]:w-[min(70%,23rem)]">
                            <button
                                wire:click="masukArena({{ $level }})"
                                wire:loading.class="opacity-60 scale-95"
                                wire:target="masukArena({{ $level }})"
                                @if ($isActive) x-ref="activeNode" @endif
                                class="touch-target group relative flex w-full items-center justify-between overflow-hidden rounded-2xl border px-4 py-3 text-left shadow-md transition-all duration-300 sm:rounded-3xl sm:px-5 sm:py-4
                                    {{ $isCompleted ? 'border-emerald-400/70 bg-emerald-300 text-emerald-900 shadow-emerald-300/30' : '' }}
                                    {{ $isActive ? 'animate-pulse-glow border-amber-500 bg-amber-400 text-amber-900 shadow-[0_10px_28px_rgba(251,191,36,0.45)] hover:scale-[1.02]' : '' }}
                                    {{ $isLocked ? 'pointer-events-none border-gray-300 bg-gray-300 text-gray-600 shadow-gray-300/30' : '' }}"
                            >
                                <span class="text-lg font-extrabold leading-none min-[520px]:text-xl sm:text-2xl">Level {{ $level }}</span>
                                <span class="rounded-full bg-white/50 p-1.5 sm:p-2">
                                    @if ($isCompleted)
                                        <x-svg-icon name="centang" class="h-6 w-6 text-emerald-700 min-[520px]:h-7 min-[520px]:w-7 sm:h-9 sm:w-9" />
                                    @elseif ($isActive)
                                        <x-svg-icon name="mahkota" class="h-6 w-6 text-orange-500 min-[520px]:h-7 min-[520px]:w-7 sm:h-9 sm:w-9" />
                                    @else
                                        <x-svg-icon name="gembok" class="h-6 w-6 text-gray-500 min-[520px]:h-7 min-[520px]:w-7 sm:h-9 sm:w-9" />
                                    @endif
                                </span>
                            </button>
                        </div>

                        @if ($level < ($playerLevel + 1))
                            <div class="pointer-events-none absolute left-1/2 top-full z-0 mt-1 h-8 w-1.5 -translate-x-1/2 rounded-full {{ $connectorDone ? 'bg-emerald-300' : 'bg-gray-300' }} sm:h-10"></div>
                        @endif
                    </div>
                @endfor
                </div>
            </div>
        </section>
    @endif

    @if ($state === 'arena')
        <section wire:key="view-arena" wire:transition.scale.duration.300ms class="flex-1 p-3 sm:p-5">
            @if ($currentChallenge)
                <div
                    wire:key="arena-{{ $currentChallenge['instance_key'] ?? $currentChallenge['question_id'] }}"
                    x-data="gameArena(@js($currentChallenge))"
                    x-init="start($wire)"
                    class="relative flex h-full flex-col overflow-hidden rounded-2xl bg-orange-50 p-3 shadow-lg transition sm:rounded-3xl sm:p-5"
                    :class="{
                        'animate-fade-in': visualState === 'playing',
                        'ring-4 ring-emerald-300': visualState === 'correct',
                        'animate-shake ring-4 ring-rose-300': visualState === 'wrong'
                    }"
                >
                    <!-- Loading Overlay -->
                    <div wire:loading.flex wire:target="challengeSelesai, kembaliKeMap" class="absolute inset-0 z-50 flex items-center justify-center bg-amber-50/70 backdrop-blur-sm">
                        <div class="flex animate-bounce flex-col items-center">
                            <x-svg-icon name="mahkota" class="h-20 w-20 text-amber-500 drop-shadow-md sm:h-28 sm:w-28" />
                            <p class="mt-4 text-2xl font-extrabold text-amber-700 drop-shadow-sm sm:mt-6 sm:text-4xl">Memuat...</p>
                        </div>
                    </div>

                    <!-- Success Overlay -->
                    <div x-show="visualState === 'success_dialog'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" class="absolute inset-0 z-40 flex flex-col items-center justify-center gap-2 p-3 bg-emerald-500/95 backdrop-blur-sm">
                        <div class="animate-bounce">
                            <x-svg-icon name="centang" class="h-14 w-14 min-[520px]:h-16 min-[520px]:w-16 sm:h-32 sm:w-32 text-white drop-shadow-md" />
                        </div>
                        <h2 class="text-center text-2xl leading-tight min-[520px]:text-3xl sm:text-4xl font-extrabold text-white drop-shadow-md">Hebat!<br>Jawabanmu Benar</h2>
                        <button type="button" @click="if(!busy) { busy = true; $wire.challengeSelesai(challenge.question_id, true, elapsed()); }" class="touch-target rounded-full bg-white px-5 py-2.5 min-[520px]:px-6 min-[520px]:py-3 sm:px-8 sm:py-4 text-lg min-[520px]:text-xl sm:text-2xl font-extrabold text-emerald-600 shadow-xl transition hover:scale-105 active:scale-95 outline-none pointer-events-auto">
                            Lanjut
                        </button>
                    </div>

                    <div class="mb-3 flex flex-col items-center justify-between gap-2 text-center min-[520px]:flex-row min-[520px]:text-left sm:mb-4 sm:flex-row sm:gap-0 sm:text-left">
                        <p class="text-lg font-extrabold leading-tight text-amber-900 min-[520px]:text-xl sm:text-3xl" x-text="challenge.prompt"></p>
                        <p class="rounded-full bg-orange-200 px-3 py-1 text-sm font-bold text-orange-700 min-[520px]:text-base sm:px-4 sm:text-xl" x-text="`Progress ${liveCorrectCount}/3`"></p>
                    </div>

                    <template x-if="challenge.engine === 'tap_collector'">
                        <div class="flex flex-1 flex-wrap items-center justify-center gap-3 p-2 perspective-1000 min-[520px]:gap-4 sm:gap-6 sm:p-4">
                            @for ($i = 0; $i < ($currentChallenge['payload']['spawn_count'] ?? 3); $i++)
                                <button
                                    class="touch-target relative flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-sky-300 to-indigo-400 shadow-[0_10px_20px_rgba(0,0,0,0.2)] transition-all duration-300 min-[520px]:h-24 min-[520px]:w-24 sm:h-32 sm:w-32"
                                    @click="tap({{ $i }})"
                                    :disabled="items[{{ $i }}]?.done"
                                    :class="items[{{ $i }}]?.done 
                                        ? 'opacity-0 scale-50 -translate-y-20 rotate-180 pointer-events-none' 
                                        : 'hover:scale-110 hover:-rotate-12 active:scale-95 animate-pulse-glow'"
                                    style="animation-delay: {{ $i * 200 }}ms; transform-style: preserve-3d;"
                                >
                                    <div class="pointer-events-none absolute inset-0 rounded-full border-2 border-white/40 sm:border-4"></div>
                                    <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-12 w-12 text-white drop-shadow-xl transition-transform min-[520px]:h-14 min-[520px]:w-14 sm:h-20 sm:w-20" x-bind:class="items[{{ $i }}]?.done ? '' : 'animate-bounce'" style="animation-delay: {{ $i * 150 }}ms" />
                                </button>
                            @endfor
                        </div>
                    </template>

                    <template x-if="challenge.engine === 'macro_dnd'">
                        <div class="flex flex-1 flex-col gap-3 p-2 min-[520px]:flex-row sm:gap-6">
                            <div class="flex w-full flex-wrap items-center justify-center gap-2 rounded-2xl bg-white/50 p-3 shadow-inner min-[520px]:w-3/5 min-[520px]:gap-3 sm:w-3/5 sm:gap-6 sm:rounded-[2rem] sm:p-6" style="min-height: 130px;">
                                @for ($i = 0; $i < ($currentChallenge['payload']['spawn_count'] ?? 3); $i++)
                                    <div
                                        class="touch-none touch-target flex h-14 w-14 cursor-grab items-center justify-center rounded-xl bg-gradient-to-br from-violet-400 to-fuchsia-400 shadow-xl transition-all duration-300 hover:scale-110 hover:-rotate-6 active:cursor-grabbing active:scale-95 min-[520px]:h-16 min-[520px]:w-16 sm:h-28 sm:w-28 sm:rounded-3xl"
                                        draggable="true"
                                        @dragstart="drag({{ $i }})"
                                        x-show="!items[{{ $i }}]?.done"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 scale-50 translate-y-10"
                                    >
                                        <div class="pointer-events-none absolute inset-0 rounded-xl border-2 border-white/30 sm:border-4 sm:rounded-3xl"></div>
                                        <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-8 w-8 text-white drop-shadow-lg min-[520px]:h-9 min-[520px]:w-9 sm:h-20 sm:w-20" />
                                    </div>
                                @endfor
                            </div>

                            <div
                                class="flex w-full items-center justify-center rounded-2xl border-4 border-dashed border-amber-400 bg-amber-100/50 p-3 shadow-lg transition-all duration-300 min-[520px]:w-2/5 sm:w-2/5 sm:rounded-[2rem] sm:border-8 sm:p-6"
                                :class="draggedIndex !== null ? 'scale-[1.02] border-amber-500 bg-amber-200/80 shadow-2xl animate-pulse' : ''"
                                @dragover.prevent
                                @drop.prevent="drop()"
                            >
                                <div class="text-center transition-transform duration-300" :class="draggedIndex !== null ? 'scale-110' : ''">
                                    <div class="mx-auto mb-2 flex h-16 w-16 items-center justify-center rounded-full bg-white/50 shadow-inner min-[520px]:h-20 min-[520px]:w-20 sm:mb-4 sm:h-40 sm:w-40">
                                        <x-svg-icon name="keranjang" class="h-10 w-10 text-orange-500 drop-shadow-md min-[520px]:h-12 min-[520px]:w-12 sm:h-28 sm:w-28" />
                                    </div>
                                    <p class="text-base font-extrabold text-amber-900 drop-shadow-sm min-[520px]:text-lg sm:text-3xl" x-text="draggedIndex !== null ? 'Lepaskan!' : 'Taruh di sini'"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="challenge.engine === 'binary_choice'">
                        <div class="flex flex-1 flex-col gap-3 p-2 min-[520px]:flex-row sm:gap-6">
                            <button class="group flex-1 rounded-3xl bg-gradient-to-b from-rose-100 to-rose-200 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.12)] transition-all duration-500 hover:-translate-y-4 hover:shadow-rose-400/50 active:scale-95 sm:rounded-[2.5rem] sm:p-8" 
                                @click="choose('left')" 
                                :class="wrongChoice === 'left' ? 'animate-shake ring-8 ring-rose-400 bg-rose-300' : ''">
                                <p class="mb-3 inline-block rounded-full bg-rose-500 px-4 py-1.5 text-base font-black tracking-widest text-white shadow-lg sm:mb-6 sm:px-8 sm:py-3 sm:text-2xl">KIRI</p>
                                <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-4">
                                    @for ($i = 0; $i < ($currentChallenge['payload']['left_count'] ?? 2); $i++)
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-md transition-transform duration-300 group-hover:rotate-12 group-hover:scale-110 sm:h-20 sm:w-20">
                                            <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-8 w-8 text-rose-500 drop-shadow-sm sm:h-12 sm:w-12" />
                                        </div>
                                    @endfor
                                </div>
                            </button>

                            <button class="group flex-1 rounded-3xl bg-gradient-to-b from-emerald-100 to-emerald-200 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.12)] transition-all duration-500 hover:-translate-y-4 hover:shadow-emerald-400/50 active:scale-95 sm:rounded-[2.5rem] sm:p-8" 
                                @click="choose('right')" 
                                :class="wrongChoice === 'right' ? 'animate-shake ring-8 ring-rose-400 bg-rose-300' : ''">
                                <p class="mb-3 inline-block rounded-full bg-emerald-500 px-4 py-1.5 text-base font-black tracking-widest text-white shadow-lg sm:mb-6 sm:px-8 sm:py-3 sm:text-2xl">KANAN</p>
                                <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-4">
                                    @for ($i = 0; $i < ($currentChallenge['payload']['right_count'] ?? 3); $i++)
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-md transition-transform duration-300 group-hover:-rotate-12 group-hover:scale-110 sm:h-20 sm:w-20">
                                            <x-svg-icon name="{{ $currentChallenge['payload']['target_asset'] ?? 'apel' }}" class="h-8 w-8 text-emerald-500 drop-shadow-sm sm:h-12 sm:w-12" />
                                        </div>
                                    @endfor
                                </div>
                            </button>
                        </div>
                    </template>

                    <div class="mt-3 sm:mt-4">
                        <button wire:click="kembaliKeMap" class="touch-target rounded-2xl bg-gray-300 px-4 py-2 text-base font-bold text-gray-700 sm:text-lg">
                            Kembali ke Peta
                        </button>
                    </div>
                </div>
            @else
                <div class="flex h-full items-center justify-center rounded-3xl bg-orange-50 shadow-lg">
                    <p class="text-lg font-bold text-orange-700 sm:text-2xl">Memuat tantangan...</p>
                </div>
            @endif
        </section>
    @endif
    </div>

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
