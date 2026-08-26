@php
    $breadcrumbs = $breadcrumbs ?? [];
    $navOpdName = Auth::user()?->isAdmin() ? 'Administrator' : (Auth::user()?->opd?->nama ?? 'OPD');
    $navUserName = Auth::user()->name ?? 'User';
    $navInitials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', trim($navUserName)), 0, 2))));
    $navRoleLabel = Auth::user()->isAdmin() ? 'Administrator' : (Auth::user()?->opd?->nama ?? 'OPD');
@endphp

<header class="sticky top-0 z-30 bg-surface/80 backdrop-blur-xl border-b border-slate-200/70">
    <div class="flex items-center justify-between gap-3 px-4 lg:px-6 h-16">
        {{-- Left: Toggle + Breadcrumb --}}
        <div class="flex items-center gap-2 min-w-0">
            {{-- Mobile hamburger --}}
            <button
                @click="mobileOpen = true"
                class="lg:hidden p-2 -ml-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-all"
                aria-label="Buka menu">
                <x-heroicon-o-bars-3 class="w-6 h-6"/>
            </button>

            {{-- Desktop collapse toggle --}}
            <button
                @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden lg:inline-flex p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-all"
                aria-label="Lipat menu">
                <x-heroicon-o-bars-3-bottom-left class="w-5 h-5"/>
            </button>

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 text-sm min-w-0">
                <span class="hidden sm:inline text-slate-400 truncate">{{ $navOpdName }}</span>
                @foreach($breadcrumbs as $index => $crumb)
                    <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                    @if($loop->last)
                        <span class="text-slate-800 font-medium truncate">{{ $crumb }}</span>
                    @else
                        <span class="hidden sm:inline text-slate-400 truncate">{{ $crumb }}</span>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Right: Actions --}}
        <div class="flex items-center gap-2 lg:gap-3 shrink-0">
            {{-- Notification --}}
            <button class="relative p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                <x-heroicon-o-bell class="w-5 h-5"/>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            {{-- Help --}}
            <button class="hidden sm:inline-flex p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                <x-heroicon-o-question-mark-circle class="w-5 h-5"/>
            </button>

            <div class="hidden sm:block w-px h-8 bg-slate-200"></div>

            {{-- Profile --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-3 pl-3 pr-2 py-1.5 hover:bg-slate-100 rounded-xl transition-all">
                    <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-xs font-semibold">
                        {{ $navInitials }}
                    </div>
                    <div class="text-left hidden md:block">
                        <div class="text-sm font-medium text-slate-800 leading-tight">{{ $navUserName }}</div>
                        <div class="text-[11px] text-slate-400">{{ $navRoleLabel }}</div>
                    </div>
                    <x-heroicon-o-chevron-down class="hidden md:block w-4 h-4 text-slate-400"/>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-200 py-2 z-50">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-all">
                        <x-heroicon-o-user class="w-4 h-4"/>
                        Profile
                    </a>
                    <div class="border-t border-slate-100 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-all">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4"/>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
