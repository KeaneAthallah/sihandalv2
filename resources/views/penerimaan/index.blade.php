<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Master Data Penerimaan" :breadcrumbs="['Keuangan', 'Master Data Penerimaan']">
            <x-slot name="actions">
                <a href="{{ route('master-data.penerimaan.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Penerimaan Baru
                </a>
                <a href="{{ route('master-data.penerimaan.import.template') }}" class="btn-secondary">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4"/>
                    Template
                </a>
                <button type="button" @click="$dispatch('open-modal', 'import-penerimaan')" class="btn-secondary">
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4"/>
                    Impor
                </button>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif
    @if($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
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
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" change="+12.5% dari bulan lalu" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Target" value="Rp {{ number_format($totalTarget / 1000000000, 1, ',', '.') }} M" change="+8.3% dari bulan lalu" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-calendar-days class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Persentase Tercapai" value="{{ $persentase }}%" change="{{ $persentase }}% dari target tahunan" changeType="up" color="info">
            <x-slot name="icon">
                <x-heroicon-o-flag class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Record" value="{{ $penerimaans->count() }}" change="Data penerimaan aktif" changeType="up" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="flex items-center gap-3 flex-wrap px-5 py-4 border-b border-border-light">
            <div class="flex items-center gap-2">
                <label class="text-sm text-content-muted font-medium">Dari</label>
                <input type="text" value="{{ request('from', '2026-01-01') }}" class="input datepicker" />
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-content-muted font-medium">Sampai</label>
                <input type="text" value="{{ request('to', '2026-12-31') }}" class="input datepicker" />
            </div>
            <select name="rekening_id" class="input">
                <option value="">Semua Rekening</option>
                @foreach($rekenings as $rek)
                    <option value="{{ $rek->id }}" {{ ($filters['rekening_id'] ?? '') == $rek->id ? 'selected' : '' }}>{{ $rek->kode }} - {{ $rek->nama }}</option>
                @endforeach
            </select>
            <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary/10 text-primary text-sm font-medium rounded-lg hover:bg-primary/20 transition">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Filter
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="border-b border-border-light">
                        <th class="text-left px-5 py-3 table-head w-[50px]">No</th>
                        <th class="text-left px-5 py-3 table-head w-[200px]">Rekening</th>
                        <th class="text-left px-5 py-3 table-head">OPD</th>
                        <th class="text-right px-5 py-3 table-head w-[160px]">Target</th>
                        <th class="text-right px-5 py-3 table-head w-[160px]">Realisasi</th>
                        <th class="text-center px-5 py-3 table-head w-[160px]">Persentase</th>
                        <th class="text-center px-5 py-3 table-head w-[100px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light">
                    @forelse($penerimaans as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-content-muted font-medium tabular-nums">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <div class="space-y-1.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary/10 text-primary whitespace-nowrap">
                                        {{ $item->rekening ? $item->rekening->kode.' - '.$item->rekening->nama : ($item->nama_penerimaan ?? '-') }}
                                    </span>
                                    @if($item->subRekening)
                                        <span class="block px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-600 whitespace-nowrap">
                                            ↳ {{ $item->subRekening->kode }} - {{ $item->subRekening->nama }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-content-secondary font-medium max-w-[220px] truncate">{{ $item->opd?->nama ?? 'Provinsi' }}</td>
                            <td class="px-5 py-3.5 font-medium tabular-nums text-content-secondary text-right whitespace-nowrap">
                                Rp {{ number_format($item->target, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-emerald-600 dark:text-emerald-400 text-right whitespace-nowrap tabular-nums">
                                Rp {{ number_format($item->realisasi, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5 justify-center">
                                    <div class="w-20 bg-surface-alt rounded-full h-2 overflow-hidden">
                                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ min($item->persentase, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-content-muted w-10 text-right tabular-nums">{{ $item->persentase }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('master-data.penerimaan.edit', $item) }}" title="Edit"
                                        class="icon-btn hover:text-amber-600 hover:dark:text-amber-400 hover:bg-amber-50 hover:dark:bg-amber-500/10">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('master-data.penerimaan.destroy', $item) }}"
                                        @submit.prevent="if(confirm('Yakin ingin menghapus data penerimaan ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                            class="icon-btn hover:text-red-600 hover:dark:text-red-400 hover:bg-red-50 hover:dark:bg-red-500/10">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-arrow-down-left class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada data penerimaan</p>
                                    <p class="empty-desc">Data penerimaan dana akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-border-light flex items-center justify-between">
            <p class="text-sm text-content-muted">Menampilkan <span class="font-medium text-content-secondary">{{ $penerimaans->total() }}</span> data penerimaan</p>
            @if(method_exists($penerimaans, 'links'))
                <div class="text-sm">
                    {{ $penerimaans->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-card>

    <x-modal name="import-penerimaan" max-width="md">
        <div class="p-6">
            <h3 class="text-base font-semibold text-content mb-1">Impor Penerimaan</h3>
            <p class="mt-1 text-xs text-content-muted mb-5">Unggah file Excel/CSV sesuai template. Satu baris per OPD, rekening, dan tahun anggaran.</p>
            <form method="POST" action="{{ route('master-data.penerimaan.import') }}" enctype="multipart/form-data">
                @csrf
                <div>
                    <x-input-label value="File Excel/CSV"/>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="input mt-1.5" required>
                    @error('file')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mt-5 flex items-center justify-end gap-3">
                    <x-secondary-button @click="$dispatch('close-modal', 'import-penerimaan')">Batal</x-secondary-button>
                    <x-primary-button>Impor</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
