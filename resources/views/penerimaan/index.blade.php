<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Penerimaan" :breadcrumbs="['Keuangan', 'Penerimaan']">
            <x-slot name="actions">
                <a href="{{ route('penerimaan.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Penerimaan Baru
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
        <div class="flex items-center gap-3 flex-wrap px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <label class="text-sm text-slate-500 font-medium">Dari</label>
                <input type="date" value="{{ request('from', '2026-01-01') }}" class="input" />
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-slate-500 font-medium">Sampai</label>
                <input type="date" value="{{ request('to', '2026-12-31') }}" class="input" />
            </div>
            <select name="sumber_dana_id" class="input">
                <option value="">Semua Sumber Dana</option>
                @foreach($sumberDanas as $sd)
                    <option value="{{ $sd->id }}" {{ $filters['sumber_dana_id'] ?? '' == $sd->id ? 'selected' : '' }}>{{ $sd->nama_sumber_dana }}</option>
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
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-5 py-3 table-head w-[50px]">No</th>
                        <th class="text-left px-5 py-3 table-head w-[120px]">Tanggal</th>
                        <th class="text-left px-5 py-3 table-head w-[150px]">Sumber Dana</th>
                        <th class="text-left px-5 py-3 table-head">OPD</th>
                        <th class="text-right px-5 py-3 table-head w-[160px]">Target</th>
                        <th class="text-right px-5 py-3 table-head w-[160px]">Realisasi</th>
                        <th class="text-center px-5 py-3 table-head w-[160px]">Persentase</th>
                        <th class="text-center px-5 py-3 table-head w-[100px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($penerimaans as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-slate-400 font-medium tabular-nums">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary/10 text-primary whitespace-nowrap">
                                    {{ $item->sumberDana?->nama_sumber_dana ?? $item->nama_sumber_dana ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700 font-medium max-w-[220px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-medium tabular-nums text-slate-700 text-right whitespace-nowrap">
                                Rp {{ number_format($item->target, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-emerald-600 text-right whitespace-nowrap tabular-nums">
                                Rp {{ number_format($item->realisasi, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5 justify-center">
                                    <div class="w-20 bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ min($item->persentase, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-slate-500 w-10 text-right tabular-nums">{{ $item->persentase }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('penerimaan.edit', $item) }}" title="Edit"
                                        class="icon-btn hover:text-amber-600 hover:bg-amber-50">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('penerimaan.destroy', $item) }}"
                                        @submit.prevent="if(confirm('Yakin ingin menghapus data penerimaan ini?')) $el.submit()">
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
                            <td colspan="8" class="px-5 py-12 text-center">
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

        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between">
            <p class="text-sm text-slate-500">Menampilkan <span class="font-medium text-slate-700">{{ $penerimaans->count() }}</span> data penerimaan</p>
            @if(method_exists($penerimaans, 'links'))
                <div class="text-sm">
                    {{ $penerimaans->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-card>
</x-app-layout>
