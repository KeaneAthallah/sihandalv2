<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Sumber Dana" :breadcrumbs="['Data Master', 'Sumber Dana']">
            <x-slot name="actions">
                <a href="{{ route('sumber-dana.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Jenis Sumber Dana" value="{{ $sumberDanas->total() }} Jenis" color="primary"/>
    </div>

    <x-card :padding="false">
        <div class="px-5 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                <input type="text" placeholder="Cari sumber dana..." class="input pl-9"/>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[600px]">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 table-head">No</th>
                        <th class="text-left px-5 py-3 table-head">Sumber Dana</th>
                        <th class="text-center px-5 py-3 table-head">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sumberDanas as $idx => $src)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-slate-400">{{ $sumberDanas->firstItem() + $idx }}</td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-slate-800">{{ $src->nama_sumber_dana }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('sumber-dana.edit', $src) }}" class="icon-btn hover:text-amber-600 hover:bg-amber-50" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form action="{{ route('sumber-dana.destroy', $src->id) }}" method="POST" class="inline" x-data @submit.prevent="if(confirm('Yakin ingin menghapus data ini?')) $el.submit()">
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
                            <td colspan="3" class="px-5 py-12 text-center text-sm text-slate-400">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-banknotes class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada data sumber dana</p>
                                    <p class="empty-desc">Data sumber dana akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sumberDanas->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-slate-400">Menampilkan {{ $sumberDanas->firstItem() }}–{{ $sumberDanas->lastItem() }} dari {{ $sumberDanas->total() }}</p>
                {{ $sumberDanas->withQueryString()->links('pagination::tailwind') }}
            </div>
        @endif
    </x-card>
</x-app-layout>
