<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Unit Pelaksana Teknis" :breadcrumbs="['Data Master', 'UPT']">
            <x-slot name="actions">
                <a href="{{ route('upt.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah UPT
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total UPT" value="{{ $upts->count() }} Unit" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-building-office-2 class="w-5 h-5"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="px-5 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                <input type="text" placeholder="Cari UPT..." class="input pl-9"/>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[600px]">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 table-head">No</th>
                        <th class="text-left px-5 py-3 table-head">Kode</th>
                        <th class="text-left px-5 py-3 table-head">Nama UPT</th>
                        <th class="text-left px-5 py-3 table-head">OPD</th>
                        <th class="text-center px-5 py-3 table-head">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($upts as $idx => $upt)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs font-medium text-slate-500">{{ $upt->kode }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-slate-800">{{ $upt->nama }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-slate-500">{{ $upt->opd?->nama }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('upt.edit', $upt) }}" class="icon-btn hover:text-amber-600 hover:bg-amber-50" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form action="{{ route('upt.destroy', $upt->id) }}" method="POST" class="inline" x-data @submit.prevent="if(confirm('Yakin ingin menghapus UPT ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn hover:text-red-600 hover:bg-red-50" title="Hapus">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-building-office-2 class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada data UPT</p>
                                    <p class="empty-desc">Data UPT akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>