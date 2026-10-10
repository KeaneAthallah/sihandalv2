<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Rekening Kas" :breadcrumbs="['Rekening Kas']">
            <x-slot name="actions">
                <a href="{{ route('rekening-kas.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Rekening
                </a>
                <a href="{{ route('rekening-kas.import.template') }}" class="btn-secondary">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4"/>
                    Template
                </a>
                <button type="button" @click="$dispatch('open-modal', 'import-rekening-kas')" class="btn-secondary">
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

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Rekening" value="{{ $rekenings->total() }}" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-wallet class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Penerimaan" value="Rp {{ number_format($totalPenerimaan / 1000000000, 1, ',', '.') }} M" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Pengeluaran" value="Rp {{ number_format($totalPengeluaran / 1000000000, 1, ',', '.') }} M" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Saldo Kas (Kas)" value="Rp {{ number_format($totalKas / 1000000000, 1, ',', '.') }} M" color="info">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="divide-y divide-border-light">
                        <th class="px-5 py-3 table-head w-16 text-left">No</th>
                        <th class="px-5 py-3 table-head w-[120px] text-left">Kode</th>
                        <th class="px-5 py-3 table-head text-left">Nama Rekening</th>
                        <th class="px-5 py-3 table-head w-[120px] text-left">Tipe</th>
                        <th class="px-5 py-3 table-head w-[160px] text-right">Total Penerimaan</th>
                        <th class="px-5 py-3 table-head w-[160px] text-right">Total Pengeluaran</th>
                        <th class="px-5 py-3 table-head w-[160px] text-right">Saldo</th>
                        <th class="px-5 py-3 table-head w-[100px] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light">
                    @forelse($rekenings as $idx => $rek)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-content-muted">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs font-semibold text-content-secondary">{{ $rek->kode }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-sm font-medium text-content {{ $rek->parent_id ? 'pl-4' : '' }}">
                                        @if($rek->parent_id)
                                            <span class="text-content-muted select-none">↳</span>
                                        @endif
                                        {{ $rek->nama }}
                                    </span>
                                    @if($rek->parent)
                                        <span class="text-xs text-content-muted">Detail dari: {{ $rek->parent->kode }} - {{ $rek->parent->nama }}</span>
                                    @endif
                                    @if($rek->children_count > 0)
                                        <span class="text-xs font-medium text-blue-500">Induk dari {{ $rek->children_count }} detail</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-lg inline-flex items-center gap-1.5
                                    {{ $rek->tipe === 'kas' ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400' : ($rek->tipe === 'pendapatan' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400') }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ ucfirst($rek->tipe) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400 tabular-nums whitespace-nowrap">
                                    Rp {{ number_format($rek->penerimaan_total, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-medium text-red-500 tabular-nums whitespace-nowrap">
                                    Rp {{ number_format($rek->pengeluaran_total, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-semibold {{ $rek->saldo_total < 0 ? 'text-red-600 dark:text-red-400' : 'text-content-secondary' }} tabular-nums whitespace-nowrap">
                                    Rp {{ number_format($rek->saldo_total, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('rekening-kas.edit', $rek) }}" class="icon-btn hover:text-amber-600 hover:dark:text-amber-400 hover:bg-amber-50 hover:dark:bg-amber-500/10" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('rekening-kas.destroy', $rek) }}" @submit.prevent="if(confirm('Yakin ingin menghapus rekening {{ addslashes($rek->nama) }}?')) $el.submit()">
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
                            <td colspan="8" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-wallet class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada data rekening</p>
                                    <p class="empty-desc">Data rekening kas akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-border-light flex items-center justify-between">
            <p class="text-sm text-content-muted">Menampilkan <span class="font-semibold text-content-secondary">{{ $rekenings->total() }}</span> rekening</p>
            @if(method_exists($rekenings, 'links'))
                <div class="text-sm">
                    {{ $rekenings->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-card>

    <x-modal name="import-rekening-kas" max-width="md">
        <div class="p-6">
            <h3 class="text-base font-semibold text-content mb-1">Impor Rekening Kas</h3>
            <p class="mt-1 text-xs text-content-muted mb-5">Unggah file Excel/CSV sesuai template. Baris dengan kode yang sama akan diperbarui.</p>
            <form method="POST" action="{{ route('rekening-kas.import') }}" enctype="multipart/form-data">
                @csrf
                <div>
                    <x-input-label value="File Excel/CSV"/>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="input mt-1.5" required>
                    @error('file')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mt-5 flex items-center justify-end gap-3">
                    <x-secondary-button @click="$dispatch('close-modal', 'import-rekening-kas')">Batal</x-secondary-button>
                    <x-primary-button>Impor</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
