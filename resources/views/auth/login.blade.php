<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-[var(--color-text)]">Masuk</h2>
        <p class="text-sm text-[var(--color-text-secondary)] mt-1">Masuk ke akun Sihandal Anda</p>
    </div>

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="ui-label">{{ __('Email') }}</label>
            <input id="email"
                class="ui-input block mt-1.5"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="nama@email.go.id" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="ui-label">{{ __('Password') }}</label>
            <input id="password"
                class="ui-input block mt-1.5"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox"
                    class="w-4 h-4 rounded border-[var(--color-border)] text-[var(--color-primary)] focus:ring-[var(--color-primary-100)]"
                    name="remember">
                <span class="text-sm text-[var(--color-text-secondary)]">{{ __('Ingat saya') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-[var(--color-primary)] hover:text-[var(--color-primary-dark)] font-medium transition-colors" href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <button type="submit"
            class="w-full px-4 py-3 bg-[var(--color-primary)] text-white text-sm font-bold rounded-lg hover:bg-[var(--color-primary-light)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-100)] transition-colors shadow-sm">
            {{ __('Masuk') }}
        </button>
    </form>
</x-guest-layout>
