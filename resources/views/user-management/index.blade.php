<x-app-layout>
    <x-slot name="header">
        <x-page-header title="User Management" :breadcrumbs="['Pengaturan', 'User Management']">
            <x-slot name="actions">
                <a href="{{ route('user-management.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-dark transition-colors">
                    <x-heroicon-o-plus class="h-4 w-4" />
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

            <x-stat-card title="Tanpa OPD" value="{{ $totalUsers - $totalAdmins - $totalOpd }}" color="slate">
                <x-slot name="icon">
                    <x-heroicon-o-eye class="h-6 w-6" />
                </x-slot>
            </x-stat-card>
        </div>

        <x-card>
            <div class="overflow-x-auto -mx-5 lg:-mx-6 px-5 lg:px-6">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50/70">
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $idx => $user)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-4 text-slate-500">{{ $users->firstItem() + $idx }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <span class="font-medium text-slate-800">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-slate-600">{{ $user->email }}</td>
                                <td class="px-4 py-4">
                                    @if($user->isAdmin())
                                        <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">Admin</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">OPD</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-slate-600">{{ $user->opd->nama ?? '-' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('user-management.edit', $user) }}" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" title="Edit">
                                            <x-heroicon-o-pencil class="h-4 w-4" />
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('user-management.destroy', $user) }}" x-data
                                                  @submit.prevent="if(confirm('Yakin ingin menghapus user ini?')) $el.submit()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Hapus">
                                                    <x-heroicon-o-trash class="h-4 w-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                        <p class="text-sm text-slate-500">Belum ada user</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-4 border-t border-slate-100">
                <p class="text-sm text-slate-500">Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data</p>
                {{ $users->links() }}
            </div>
        </x-card>
    </div>
</x-app-layout>
