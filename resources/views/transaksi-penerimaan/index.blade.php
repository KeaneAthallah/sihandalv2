<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Transaksi Penerimaan" :breadcrumbs="['Keuangan', 'Transaksi Penerimaan']">
            <x-slot name="actions">
                <a href="{{ route('transaksi-penerimaan.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Transaksi Baru
                </a>
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

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-5">
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" change="Dari seluruh transaksi" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Transaksi" value="{{ $transaksis->count() }}" change="Transaksi Penerimaan aktif" changeType="up" color="info">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="flex items-center gap-3 flex-wrap px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <label class="text-sm text-slate-500 font-medium">Sumber Dana</label>
                <select name="penerimaan_id" class="input" onchange="if(this.value) window.location.href = '{{ url('transaksi-penerimaan') }}?penerimaan_id=' + this.value; else window.location.href = '{{ url('transaksi-penerimaan') }}';">
                    <option value="">Semua</option>
                    @foreach($penerimaans as $p)
                        <option value="{{ $p->id }}" {{ ($filters['penerimaan_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->sumberDana?->nama_sumber_dana ?? $p->nama_sumber_dana }} ({{ $p->opd?->nama ?? 'Provinsi' }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-5 py-3 table-head w-[50px]">No</th>
                        <th class="text-left px-5 py-3 table-head w-[130px]">Tanggal</th>
                        <th class="text-left px-5 py-3 table-head w-[180px]">Sumber Dana</th>
                        <th class="text-left px-5 py-3 table-head">OPD</th>
                        <th class="text-right px-5 py-3 table-head w-[160px]">Realisasi</th>
                        <th class="text-left px-5 py-3 table-head">Keterangan</th>
                        <th class="text-center px-5 py-3 table-head w-[100px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksis as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-slate-400 font-medium tabular-nums">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary/10 text-primary whitespace-nowrap">
                                    {{ $item->penerimaan?->sumberDana?->nama_sumber_dana ?? $item->penerimaan?->nama_sumber_dana ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700 font-medium max-w-[220px] truncate">{{ $item->penerimaan?->opd?->nama ?? 'Provinsi' }}</td>
                            <td class="px-5 py-3.5 font-medium text-emerald-600 text-right whitespace-nowrap tabular-nums">
                                Rp {{ number_format($item->realisasi, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 max-w-[260px] truncate">{{ $item->keterangan ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('transaksi-penerimaan.edit', $item) }}" title="Edit"
                                        class="icon-btn hover:text-amber-600 hover:bg-amber-50">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('transaksi-penerimaan.destroy', $item) }}"
                                        @submit.prevent="if(confirm('Yakin ingin menghapus transaksi ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                            class="icon-btn hover:text-red-600 hover:bg-red-50">
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
                                        <x-heroicon-o-banknotes class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada transaksi penerimaan</p>
                                    <p class="empty-desc">Transaksi realisasi penerimaan akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>