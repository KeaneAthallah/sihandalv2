<x-app-layout>
    <x-slot name="header">
        <x-page-header title="User Management" :breadcrumbs="['Pengaturan', 'User Management']">
            <x-slot name="actions">
                <a href="{{ route('user-management.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah User
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    @if($errors->any())
        <x-alert type="danger">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">
            <x-stat-card title="Total User" value="{{ $totalUsers }}" color="primary">
                <x-slot name="icon">
                    <x-heroicon-o-users class="h-6 w-6" />
                </x-slot>
            </x-stat-card>

            <x-stat-card title="Admin" value="{{ $totalAdmins }}" color="danger">
                <x-slot name="icon">
                    <x-heroicon-o-shield-check class="h-6 w-6" />
                </x-slot>
            </x-stat-card>

            <x-stat-card title="OPD" value="{{ $totalOpd }}" color="warning">
                <x-slot name="icon">
                    <x-heroicon-o-building-office-2 class="h-6 w-6" />
                </x-slot>
            </x-stat-card>

            <x-stat-card title="Tanpa OPD" value="{{ $totalUsers - $totalAdmins - $totalOpd }}" color="info">
                <x-slot name="icon">
                    <x-heroicon-o-user class="h-6 w-6" />
                </x-slot>
            </x-stat-card>
        </div>

        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="border-b border-border-light">
                            <th class="px-5 py-3 table-head">No</th>
                            <th class="px-5 py-3 table-head">Nama</th>
                            <th class="px-5 py-3 table-head">Email</th>
                            <th class="px-5 py-3 table-head">Role</th>
                            <th class="px-5 py-3 table-head">OPD</th>
                            <th class="px-5 py-3 table-head text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light">
                        @forelse($users as $idx => $user)
                            <tr class="table-row">
                                <td class="px-5 py-3 text-content-muted font-medium">{{ $users->firstItem() + $idx }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $avatarColors = [
                                                'admin' => 'bg-red-500',
                                                'opd' => 'bg-primary',
                                            ];
                                            $avatarColor = $avatarColors[$user->role] ?? 'bg-slate-400';
                                        @endphp
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $avatarColor }} text-xs font-bold text-white">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-content truncate">{{ $user->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-content-muted">{{ $user->email }}</td>
                                <td class="px-5 py-3">
                                    @if($user->isAdmin())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 dark:bg-red-500/10 px-2.5 py-1 text-xs font-semibold text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-600/20">
                                            Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-600/20">
                                            OPD
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-content-secondary">{{ $user->opd->nama ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('user-management.edit', $user) }}" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-content-muted hover:bg-surface-alt hover:text-content-secondary transition-colors">
                                            Edit
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <span class="text-border-strong">|</span>
                                            <form method="POST" action="{{ route('user-management.destroy', $user) }}" x-data
                                                  @submit.prevent="if(confirm('Yakin ingin menghapus user ini?')) $el.submit()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-content-muted hover:bg-red-50 hover:dark:bg-red-500/10 hover:text-red-600 hover:dark:text-red-400 transition-colors">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-surface-alt">
                                            <x-heroicon-o-users class="h-7 w-7 text-content-muted"/>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-content-muted">Belum ada user terdaftar</p>
                                            <p class="text-xs text-content-muted mt-1">Klik "Tambah User" untuk menambahkan user baru</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-5 py-4 border-t border-border-light flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <p class="text-sm text-content-muted">Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data</p>
                    {{ $users->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
