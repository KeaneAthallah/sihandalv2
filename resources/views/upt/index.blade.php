<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Unit Pelaksana Teknis" :breadcrumbs="['Data Master', 'UPT']">
            <x-slot name="actions">
                <a href="{{ route('upt.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah UPT
                </a>
                <a href="{{ route('upt.import.template') }}" class="btn-secondary">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4"/>
                    Template
                </a>
                <button type="button" @click="$dispatch('open-modal', 'import-upt')" class="btn-secondary">
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4"/>
                    Impor
                </button>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    @if(session('import_errors'))
        <x-alert type="error" :dismissible="true" title="Impor gagal">
            <ul class="list-disc list-inside space-y-1 max-h-60 overflow-y-auto">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total UPT" value="{{ $upts->total() }} Unit" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-building-office-2 class="w-5 h-5"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="px-5 py-3 border-b border-border-light flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-1/2 -translate-y-1/2"/>
                <input type="text" placeholder="Cari UPT..." class="input pl-9"/>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[600px]">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left px-5 py-3 table-head">No</th>
                        <th class="text-left px-5 py-3 table-head">Kode</th>
                        <th class="text-left px-5 py-3 table-head">Nama UPT</th>
                        <th class="text-left px-5 py-3 table-head">OPD</th>
                        <th class="text-center px-5 py-3 table-head">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light">
                    @forelse($upts as $idx => $upt)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-content-muted">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs font-medium text-content-muted">{{ $upt->kode }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-content">{{ $upt->nama }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-content-muted">{{ $upt->opd?->nama }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('upt.edit', $upt) }}" class="icon-btn hover:text-amber-600 hover:dark:text-amber-400 hover:bg-amber-50 hover:dark:bg-amber-500/10" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form action="{{ route('upt.destroy', $upt->id) }}" method="POST" class="inline" x-data @submit.prevent="if(confirm('Yakin ingin menghapus UPT ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn hover:text-red-600 hover:dark:text-red-400 hover:bg-red-50 hover:dark:bg-red-500/10" title="Hapus">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-content-muted">
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

        <div class="px-5 py-3 border-t border-border-light flex items-center justify-between">
            <p class="text-sm text-content-muted">Menampilkan <span class="font-medium text-content-secondary">{{ $upts->total() }}</span> UPT</p>
            @if(method_exists($upts, 'links'))
                <div class="text-sm">
                    {{ $upts->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-card>

    <x-modal name="import-upt" max-width="md">
        <div class="p-6">
            <h3 class="text-base font-semibold text-content mb-1">Impor UPT</h3>
            <p class="mt-1 text-xs text-content-muted mb-5">Unggah file Excel/CSV sesuai template. Baris dengan kode yang sama akan diperbarui.</p>
            <form method="POST" action="{{ route('upt.import') }}" enctype="multipart/form-data">
                @csrf
                <div>
                    <x-input-label value="File Excel/CSV"/>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="input mt-1.5" required>
                    @error('file')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mt-5 flex items-center justify-end gap-3">
                    <x-secondary-button @click="$dispatch('close-modal', 'import-upt')">Batal</x-secondary-button>
                    <x-primary-button>Impor</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>