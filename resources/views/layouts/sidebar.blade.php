@php
    $currentRoute = request()->route()?->getName() ?? '';
    $isAdmin = Auth::user()?->isAdmin() ?? false;
    $pendingCount = \App\Models\PermintaanDana::where('status', 'menunggu')
        ->when(! $isAdmin, fn ($q) => $q->where('opd_id', Auth::user()->opd_id))
        ->count();
@endphp

<aside
    :class="{
        'translate-x-0': mobileOpen,
        '-translate-x-full lg:translate-x-0': !mobileOpen,
        'lg:w-[76px]': sidebarCollapsed,
        'lg:w-[260px]': !sidebarCollapsed
    }"
    class="fixed inset-y-0 left-0 z-50 w-[260px] bg-primary text-white flex flex-col transition-all duration-300 ease-in-out overflow-y-auto overflow-x-hidden"
>
    {{-- Logo --}}
    <div class="px-4 lg:px-5 py-5 border-b border-white/10 shrink-0">
        <div :class="sidebarCollapsed ? 'lg:justify-center' : 'lg:justify-start'" class="flex items-center gap-3">
            <img src="{{ asset('logo.png') }}" alt="Sihandal" class="w-14 h-14 rounded-xl object-contain bg-white/20 p-2 shrink-0" />
            <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="min-w-0">
                <div class="font-bold text-lg leading-tight">Sihandal</div>
                <div class="text-xs text-white/60">Sistem Informasi Keuangan Daerah</div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-1">
        @php
            $linkBase = 'group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all';
            $activeClass = 'bg-white/20 text-white shadow-sm';
            $inactiveClass = 'text-white/70 hover:bg-white/10 hover:text-white';
        @endphp

        <a href="{{ route('dashboard') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-home class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Dashboard</span>
        </a>

        {{-- MASTER DATA --}}
        <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="pt-4 pb-2 px-3">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-white/40">Master Data</span>
        </div>
        <a href="{{ route('sumber-dana.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('sumber-dana.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-banknotes class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Sumber Dana</span>
        </a>
        <a href="{{ route('opd.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('opd.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-building-office-2 class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">OPD</span>
        </a>
        <a href="{{ route('rekening-kas.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('rekening-kas.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-wallet class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Rekening Kas</span>
        </a>
        <a href="{{ route('program-kegiatan.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('program-kegiatan.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-list-bullet class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Program & Kegiatan</span>
        </a>

        {{-- PENGELOLAAN DANA --}}
        <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="pt-4 pb-2 px-3">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-white/40">Pengelolaan Dana</span>
        </div>
        <a href="{{ route('posisi-kas.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('posisi-kas.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-currency-dollar class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Posisi Kas</span>
        </a>
        <a href="{{ route('penerimaan.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('penerimaan.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-arrow-down-left class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Penerimaan</span>
        </a>
        <a href="{{ route('pengeluaran.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('pengeluaran.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-arrow-up-right class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Pengeluaran</span>
        </a>
        <a href="{{ route('permintaan-dana.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('permintaan-dana.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-document-text class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Permintaan Dana</span>
            @if($pendingCount > 0)
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[20px] text-center">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('persetujuan.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('persetujuan.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-check-circle class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Persetujuan</span>
        </a>
        <a href="{{ route('transfer-dana.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('transfer-dana.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Transfer Dana</span>
        </a>

        {{-- LAPORAN --}}
        <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="pt-4 pb-2 px-3">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-white/40">Laporan</span>
        </div>
        <a href="{{ route('laporan-penerimaan.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('laporan-penerimaan.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-document-chart-bar class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Laporan Penerimaan</span>
        </a>
        <a href="{{ route('laporan-pengeluaran.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('laporan-pengeluaran.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-document-text class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Laporan Pengeluaran</span>
        </a>
        <a href="{{ route('laporan-posisi-kas.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('laporan-posisi-kas.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-banknotes class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Laporan Posisi Kas</span>
        </a>
        <a href="{{ route('rekap-permintaan-dana.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
           class="{{ $linkBase }} {{ request()->routeIs('rekap-permintaan-dana.*') ? $activeClass : $inactiveClass }}">
            <x-heroicon-o-clipboard-document-list class="w-5 h-5 shrink-0"/>
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Rekap Permintaan Dana</span>
        </a>

        {{-- PENGATURAN --}}
        @if($isAdmin)
            <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="pt-4 pb-2 px-3">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-white/40">Pengaturan</span>
            </div>
            <a href="{{ route('user-management.index') }}"
               :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
               class="{{ $linkBase }} {{ request()->routeIs('user-management.*') ? $activeClass : $inactiveClass }}">
                <x-heroicon-o-users class="w-5 h-5 shrink-0"/>
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">User Management</span>
            </a>
            <a href="{{ route('pengaturan.index') }}"
               :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : ''"
               class="{{ $linkBase }} {{ request()->routeIs('pengaturan.*') ? $activeClass : $inactiveClass }}">
                <x-heroicon-o-cog-6-tooth class="w-5 h-5 shrink-0"/>
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">Pengaturan</span>
            </a>
        @endif
    </nav>

    {{-- Bottom User Info --}}
    <div class="px-4 py-4 border-t border-white/10 shrink-0">
        @php
            $userName = Auth::user()->name ?? 'User';
            $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', trim($userName)), 0, 2))));
            $roleLabel = $isAdmin ? 'Administrator' : (Auth::user()->opd?->nama ?? 'OPD');
        @endphp
        <div :class="sidebarCollapsed ? 'lg:justify-center' : 'lg:justify-start'" class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center text-sm font-semibold shrink-0">
                {{ $initials }}
            </div>
            <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="flex-1 min-w-0">
                <div class="text-sm font-medium truncate">{{ $userName }}</div>
                <div class="text-[11px] text-white/50 truncate">{{ $roleLabel }}</div>
            </div>
        </div>
    </div>
</aside>
