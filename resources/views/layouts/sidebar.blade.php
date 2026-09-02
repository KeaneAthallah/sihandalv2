@php
    $currentRoute = request()->route()?->getName() ?? '';
    $isAdmin = Auth::user()?->isAdmin() ?? false;
    $pendingCount = \App\Models\PermintaanDana::where('status', 'menunggu')
        ->when(! $isAdmin, fn ($q) => $q->where('opd_id', Auth::user()?->opd_id))
        ->count();

    $isActive = fn (string $route) => request()->routeIs($route);
    $isSectionActive = fn (array $routes) => collect($routes)->contains(fn ($r) => request()->routeIs($r));

    $linkClass = <<<'blade'
        group relative flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium
        transition-all duration-200 ease-in-out
    blade;

    $activeBg = 'bg-white/15 text-white';
    $inactiveBg = 'text-white/60 hover:bg-white/[0.08] hover:text-white/90';

    $userName = Auth::user()->name ?? 'User';
    $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', trim($userName)), 0, 2))));
    $roleLabel = $isAdmin ? 'Administrator' : (Auth::user()?->opd?->nama ?? 'OPD');
@endphp

<aside
    :class="{
        'translate-x-0': mobileOpen,
        '-translate-x-full lg:translate-x-0': !mobileOpen,
        'lg:w-[64px]': sidebarCollapsed,
        'lg:w-[248px]': !sidebarCollapsed
    }"
    class="fixed inset-y-0 left-0 z-50 w-[248px] bg-primary text-white flex flex-col transition-all duration-300 ease-in-out overflow-hidden"
>
    {{-- Logo --}}
    <div
        :class="sidebarCollapsed ? 'px-[13px]' : 'px-4'"
        class="shrink-0 flex items-center gap-3 py-4 border-b border-white/[0.08] transition-all duration-300"
    >
        <div class="relative shrink-0">
            <img
                src="{{ asset('logo.png') }}"
                alt="Sihandal"
                class="w-9 h-9 rounded-lg object-contain bg-white/15 p-[5px]"
            />
        </div>
        <div
            :class="sidebarCollapsed ? 'lg:hidden opacity-0 w-0' : 'opacity-100'"
            class="min-w-0 transition-all duration-300 overflow-hidden"
        >
            <div class="text-[15px] font-bold tracking-tight leading-tight">Sihandal</div>
            <div class="text-[10px] text-white/45 font-medium tracking-wide uppercase mt-0.5">Keuangan Daerah</div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-2.5 py-3 space-y-0.5 custom-scrollbar">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('dashboard') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-home class="shrink-0" :class="$isActive('dashboard') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Dashboard</span>
            @if($isActive('dashboard'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        {{-- MASTER DATA --}}
        <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="pt-4 pb-1 px-3 transition-opacity duration-200">
            <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-white/30">Master Data</span>
        </div>

        <a href="{{ route('sumber-dana.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('sumber-dana.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-banknotes class="shrink-0" :class="$isActive('sumber-dana.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Sumber Dana</span>
            @if($isActive('sumber-dana.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('opd.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('opd.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-building-office-2 class="shrink-0" :class="$isActive('opd.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">OPD</span>
            @if($isActive('opd.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('upt.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('upt.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-building-library class="shrink-0" :class="$isActive('upt.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">UPT</span>
            @if($isActive('upt.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('rekening-kas.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('rekening-kas.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-wallet class="shrink-0" :class="$isActive('rekening-kas.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Rekening Kas</span>
            @if($isActive('rekening-kas.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('program-kegiatan.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('program-kegiatan.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-list-bullet class="shrink-0" :class="$isActive('program-kegiatan.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Program & Kegiatan</span>
            @if($isActive('program-kegiatan.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        {{-- TRANSAKSI --}}
        <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="pt-4 pb-1 px-3 transition-opacity duration-200">
            <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-white/30">Transaksi</span>
        </div>

        <a href="{{ route('posisi-kas.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('posisi-kas.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-currency-dollar class="shrink-0" :class="$isActive('posisi-kas.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Posisi Kas</span>
            @if($isActive('posisi-kas.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('penerimaan.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('penerimaan.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-arrow-down-left class="shrink-0" :class="$isActive('penerimaan.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Penerimaan</span>
            @if($isActive('penerimaan.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('pengeluaran.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('pengeluaran.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-arrow-up-right class="shrink-0" :class="$isActive('pengeluaran.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Pengeluaran</span>
            @if($isActive('pengeluaran.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('permintaan-dana.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('permintaan-dana.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-document-text class="shrink-0" :class="$isActive('permintaan-dana.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Permintaan Dana</span>
            @if($pendingCount > 0)
                <span :class="sidebarCollapsed ? 'hidden' : ''" class="ml-auto bg-red-500/90 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center leading-none backdrop-blur-sm">{{ $pendingCount }}</span>
                <span :class="sidebarCollapsed ? '' : 'hidden'" class="absolute top-1.5 right-2.5 w-2 h-2 bg-red-400 rounded-full ring-2 ring-primary-800"></span>
            @endif
            @if($isActive('permintaan-dana.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('persetujuan.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('persetujuan.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-check-circle class="shrink-0" :class="$isActive('persetujuan.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Persetujuan</span>
            @if($isActive('persetujuan.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('transfer-dana.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('transfer-dana.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-arrows-right-left class="shrink-0" :class="$isActive('transfer-dana.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Transfer Dana</span>
            @if($isActive('transfer-dana.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        {{-- LAPORAN --}}
        <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="pt-4 pb-1 px-3 transition-opacity duration-200">
            <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-white/30">Laporan</span>
        </div>

        <a href="{{ route('laporan-penerimaan.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('laporan-penerimaan.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-document-chart-bar class="shrink-0" :class="$isActive('laporan-penerimaan.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Laporan Penerimaan</span>
            @if($isActive('laporan-penerimaan.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('laporan-pengeluaran.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('laporan-pengeluaran.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-document-text class="shrink-0" :class="$isActive('laporan-pengeluaran.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Laporan Pengeluaran</span>
            @if($isActive('laporan-pengeluaran.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('laporan-posisi-kas.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('laporan-posisi-kas.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-banknotes class="shrink-0" :class="$isActive('laporan-posisi-kas.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Laporan Posisi Kas</span>
            @if($isActive('laporan-posisi-kas.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        <a href="{{ route('rekap-permintaan-dana.index') }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
           class="{{ $linkClass }} {{ $isActive('rekap-permintaan-dana.*') ? $activeBg : $inactiveBg }}">
            <x-heroicon-o-clipboard-document-list class="shrink-0" :class="$isActive('rekap-permintaan-dana.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
            <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Rekap Permintaan Dana</span>
            @if($isActive('rekap-permintaan-dana.*'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
            @endif
        </a>

        {{-- ADMIN --}}
        @if($isAdmin)
            <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="pt-4 pb-1 px-3 transition-opacity duration-200">
                <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-white/30">Admin</span>
            </div>

            <a href="{{ route('user-management.index') }}"
               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
               class="{{ $linkClass }} {{ $isActive('user-management.*') ? $activeBg : $inactiveBg }}">
                <x-heroicon-o-users class="shrink-0" :class="$isActive('user-management.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">User Management</span>
                @if($isActive('user-management.*'))
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
                @endif
            </a>

            <a href="{{ route('tahun-anggaran.index') }}"
               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
               class="{{ $linkClass }} {{ $isActive('tahun-anggaran.*') ? $activeBg : $inactiveBg }}">
                <x-heroicon-o-calendar class="shrink-0" :class="$isActive('tahun-anggaran.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Tahun Anggaran</span>
                @if($isActive('tahun-anggaran.*'))
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
                @endif
            </a>

            <a href="{{ route('pengaturan.index') }}"
               :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
               class="{{ $linkClass }} {{ $isActive('pengaturan.*') ? $activeBg : $inactiveBg }}">
                <x-heroicon-o-cog-6-tooth class="shrink-0" :class="$isActive('pengaturan.*') ? 'w-4 h-4 text-white' : 'w-4 h-4 text-white/50 group-hover:text-white/70'" />
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate transition-opacity duration-200">Pengaturan</span>
                @if($isActive('pengaturan.*'))
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-white rounded-r-full"></div>
                @endif
            </a>
        @endif
    </nav>

    {{-- User Info --}}
    <div
        :class="sidebarCollapsed ? 'px-[13px] py-3' : 'px-4 py-3.5'"
        class="shrink-0 border-t border-white/[0.08] transition-all duration-300"
    >
        <div :class="sidebarCollapsed ? 'justify-center' : ''" class="flex items-center gap-2.5">
            <div class="relative shrink-0">
                <div class="w-8 h-8 bg-white/15 rounded-full flex items-center justify-center text-[11px] font-bold">
                    {{ $initials }}
                </div>
            </div>
            <div
                :class="sidebarCollapsed ? 'lg:hidden opacity-0 w-0' : 'opacity-100'"
                class="flex-1 min-w-0 transition-all duration-300 overflow-hidden"
            >
                <div class="text-[13px] font-semibold text-white truncate leading-tight">{{ $userName }}</div>
                <div class="text-[11px] text-white/40 truncate mt-0.5">{{ $roleLabel }}</div>
            </div>
        </div>
    </div>
</aside>


