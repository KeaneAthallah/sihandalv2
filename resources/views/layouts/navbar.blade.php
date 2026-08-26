@php
    $breadcrumbs = $breadcrumbs ?? [];
    $navOpdName = Auth::user()?->isAdmin() ? 'Administrator' : (Auth::user()?->opd?->nama ?? 'OPD');
    $navUserName = Auth::user()->name ?? 'User';
    $navInitials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', trim($navUserName)), 0, 2))));
    $navRoleLabel = Auth::user()->isAdmin() ? 'Administrator' : (Auth::user()?->opd?->nama ?? 'OPD');
    $unreadNotificationCount = Auth::user()?->unreadNotifications()->count() ?? 0;
@endphp

<header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80" x-data>
    <div class="flex items-center justify-between h-16 px-4 lg:px-6">

        {{-- Left: Navigation controls + Breadcrumbs --}}
        <div class="flex items-center gap-1 min-w-0">
            {{-- Mobile hamburger --}}
            <button
                @click="mobileOpen = true"
                class="lg:hidden p-2 -ml-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition"
                aria-label="Buka menu">
                <x-heroicon-o-bars-3 class="w-5 h-5"/>
            </button>

            {{-- Desktop sidebar collapse --}}
            <button
                @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden lg:inline-flex p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition"
                aria-label="Lipat menu">
                <x-heroicon-o-bars-3-bottom-left class="w-5 h-5"/>
            </button>

            {{-- Breadcrumbs --}}
            <nav class="hidden sm:flex items-center gap-1.5 ml-2 text-sm min-w-0" aria-label="Breadcrumb">
                {{-- OPD label as root --}}
                <span class="text-slate-400 font-medium truncate max-w-[160px]">{{ $navOpdName }}</span>

                @foreach($breadcrumbs as $crumb)
                    <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>

                    @if($loop->last)
                        <span class="text-slate-800 font-semibold truncate max-w-[200px]">{{ $crumb }}</span>
                    @else
                        <span class="text-slate-400 truncate max-w-[140px]">{{ $crumb }}</span>
                    @endif
                @endforeach
            </nav>
        </div>

        {{-- Right: Actions --}}
        <div class="flex items-center gap-1 shrink-0">

            {{-- Search shortcut (placeholder) --}}
            <button
                class="hidden md:inline-flex items-center gap-2 px-3 py-1.5 text-xs text-slate-400 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-lg transition"
                title="Cari (Ctrl+K)">
                <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5"/>
                <span>Cari</span>
                <kbd class="ml-1 px-1.5 py-0.5 text-[10px] font-medium text-slate-400 bg-white border border-slate-200 rounded">⌘K</kbd>
            </button>

            {{-- Notifications --}}
            <a
                href="{{ route('notifications.index') }}"
                class="relative p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition"
                aria-label="Notifikasi">
                <x-heroicon-o-bell class="w-5 h-5"/>
                @if($unreadNotificationCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full ring-2 ring-white">
                        {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                    </span>
                @endif
            </a>

            {{-- Divider --}}
            <div class="hidden sm:block w-px h-6 bg-slate-200 mx-1.5"></div>

            {{-- Profile dropdown --}}
            <div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
                <button
                    @click="open = !open"
                    class="flex items-center gap-2.5 p-1.5 hover:bg-slate-100 rounded-lg transition"
                    x-bind:class="open && 'bg-slate-100'">
                    {{-- Avatar --}}
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold ring-2 ring-primary/20">
                        {{ $navInitials }}
                    </div>

                    {{-- Name + Role (desktop) --}}
                    <div class="hidden lg:flex flex-col text-left min-w-0">
                        <span class="text-sm font-semibold text-slate-800 leading-tight truncate max-w-[140px]">{{ $navUserName }}</span>
                        <span class="text-[11px] font-medium text-slate-400 leading-tight">
                            @if(Auth::user()->isAdmin())
                                <span class="inline-flex items-center px-1.5 py-0 rounded bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wide">Admin</span>
                            @else
                                <span class="truncate inline-block max-w-[130px]">{{ $navRoleLabel }}</span>
                            @endif
                        </span>
                    </div>

                    <x-heroicon-o-chevron-down class="hidden lg:block w-4 h-4 text-slate-400 shrink-0 transition-transform" x-bind:class="open && 'rotate-180'"/>
                </button>

                {{-- Dropdown --}}
                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                    @click.away="open = false"
                    class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-xl shadow-slate-200/50 border border-slate-200/80 py-1.5 z-50">

                    {{-- User info header --}}
                    <div class="px-4 py-3 border-b border-slate-100">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $navUserName }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            @if(Auth::user()->isAdmin())
                                <span class="inline-flex items-center px-1.5 py-0 rounded bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wide">Admin</span>
                            @else
                                {{ $navOpdName }}
                            @endif
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="py-1.5">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:text-slate-800 hover:bg-slate-50 transition">
                            <x-heroicon-o-user-circle class="w-4.5 h-4.5 text-slate-400"/>
                            Profil Saya
                        </a>
                    </div>

                    {{-- Logout --}}
                    <div class="border-t border-slate-100 pt-1.5">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                <x-heroicon-o-arrow-right-on-rectangle class="w-4.5 h-4.5"/>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
