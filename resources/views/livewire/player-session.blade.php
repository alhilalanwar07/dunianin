<div
    class="game-area flex h-full w-full items-center justify-center bg-amber-50 p-6"
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
        <div class="flex flex-col items-center gap-4 rounded-3xl bg-orange-50 p-10 shadow-lg">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-amber-300 border-t-orange-500"></div>
            <p class="text-2xl font-bold text-amber-900">Memuat profil pemain...</p>
        </div>
    @else
        <form wire:submit="register" class="w-full max-w-2xl rounded-3xl bg-orange-50 p-10 shadow-xl">
            <h1 class="text-5xl font-extrabold text-amber-900">Dunia Anin</h1>
            <p class="mt-3 text-2xl font-semibold text-orange-700">Siapa namamu?</p>

            <input
                wire:model.live.debounce.250ms="username"
                type="text"
                maxlength="50"
                class="mt-6 h-16 w-full rounded-2xl border-2 border-amber-300 bg-white px-5 text-2xl text-amber-900 outline-none focus:border-orange-500"
                placeholder="Masukkan nama"
                autocomplete="off"
            >

            @error('username')
                <p class="mt-3 text-lg font-semibold text-rose-500">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="touch-target mt-8 min-h-16 min-w-40 rounded-3xl bg-amber-400 px-8 py-3 text-2xl font-extrabold text-amber-900 shadow-lg transition hover:scale-105 hover:bg-orange-500 hover:text-white"
            >
                Mulai Main
            </button>
        </form>
    @endif
</div>
