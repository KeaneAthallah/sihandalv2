<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800">Selamat Datang</h2>
        <p class="text-sm text-slate-500 mt-1">Masuk ke akun Sihandal Anda</p>
    </div>

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-sm font-medium text-slate-700" />
            <x-text-input id="email" class="block mt-2 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-slate-700" />
            <x-text-input id="password" class="block mt-2 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                type="password"
                name="password"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20" name="remember">
                <span class="text-sm text-slate-600">{{ __('Ingat saya') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-primary hover:text-primary-dark font-medium transition-all" href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="w-full px-4 py-3 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all shadow-sm">
            {{ __('Masuk') }}
        </button>
    </form>
</x-guest-layout>
