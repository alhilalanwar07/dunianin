<div
    class="game-area flex h-full w-full items-center justify-center bg-amber-50 p-4 md:p-6"
    x-data
    x-init="
        const token = localStorage.getItem('dunia_anin_session_token');
        if (token) {
            $wire.resumeWithToken(token);
        } else {
            $wire.finishChecking();
        }
    "
    x-on:session-created.window="localStorage.setItem('dunia_anin_session_token', $event.detail.token)"
    x-on:session-invalid.window="localStorage.removeItem('dunia_anin_session_token')"
>
    @if ($isCheckingSession)
        <div class="flex flex-col items-center gap-3 rounded-3xl bg-orange-50 p-6 shadow-lg md:gap-4 md:p-10">
            <div class="h-10 w-10 animate-spin rounded-full border-4 border-amber-300 border-t-orange-500 md:h-12 md:w-12"></div>
            <p class="text-lg font-bold text-amber-900 md:text-2xl">Memuat profil pemain...</p>
        </div>
    @else
        <form wire:submit="register" class="w-full max-w-xl rounded-3xl bg-orange-50 p-5 shadow-xl md:max-w-2xl md:p-10">
            <h1 class="text-3xl font-extrabold text-amber-900 md:text-5xl">Dunia Anin</h1>
            <p class="mt-2 text-lg font-semibold text-orange-700 md:mt-3 md:text-2xl">Siapa namamu?</p>

            <input
                wire:model.live.debounce.250ms="username"
                type="text"
                maxlength="50"
                class="mt-4 h-14 w-full rounded-2xl border-2 border-amber-300 bg-white px-4 text-lg text-amber-900 outline-none focus:border-orange-500 md:mt-6 md:h-16 md:px-5 md:text-2xl"
                placeholder="Masukkan nama"
                autocomplete="off"
            >

            @error('username')
                <p class="mt-2 text-sm font-semibold text-rose-500 md:mt-3 md:text-lg">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="touch-target mt-6 min-h-14 min-w-32 rounded-3xl bg-amber-400 px-6 py-3 text-lg font-extrabold text-amber-900 shadow-lg transition hover:scale-105 hover:bg-orange-500 hover:text-white md:mt-8 md:min-h-16 md:min-w-40 md:px-8 md:text-2xl"
            >
                Mulai Main
            </button>
        </form>
    @endif
</div>
