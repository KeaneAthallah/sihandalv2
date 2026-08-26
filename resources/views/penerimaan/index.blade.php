<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Penerimaan" :breadcrumbs="['Keuangan', 'Penerimaan']">
            <x-slot name="actions">
                <a href="{{ route('penerimaan.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
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

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
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

    <x-filter-bar>
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-500 font-medium">Dari</label>
            <input type="date" value="{{ request('from', '2026-01-01') }}"
                class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-500 font-medium">Sampai</label>
            <input type="date" value="{{ request('to', '2026-12-31') }}"
                class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
        </div>
        <select class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
            <option>Semua Sumber Dana</option>
            @foreach($penerimaans->pluck('nama_sumber_dana')->unique()->filter() as $sd)
                <option>{{ $sd }}</option>
            @endforeach
        </select>
        <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary/10 text-primary text-sm font-semibold rounded-xl hover:bg-primary/20 transition-all">
            <x-heroicon-o-funnel class="w-4 h-4"/>
            Filter
        </button>
    </x-filter-bar>

    <x-card>
        <div class="overflow-x-auto -mx-5 lg:-mx-6">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[50px]">No</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[120px]">Tanggal</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[150px]">Sumber Dana</th>
                        <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">OPD</th>
                        <th class="text-right px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[160px]">Target</th>
                        <th class="text-right px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[160px]">Realisasi</th>
                        <th class="text-center px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[160px]">Persentase</th>
                        <th class="text-center px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[100px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($penerimaans as $idx => $item)
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <td class="px-6 py-4 text-slate-400 font-medium tabular-nums">{{ $idx + 1 }}</td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary/10 text-primary whitespace-nowrap">
                                    {{ $item->nama_sumber_dana ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-700 font-medium max-w-[220px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-700 text-right whitespace-nowrap tabular-nums">
                                Rp {{ number_format($item->target, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-emerald-600 text-right whitespace-nowrap tabular-nums">
                                Rp {{ number_format($item->realisasi, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5 justify-center">
                                    <div class="w-20 bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ min($item->persentase, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500 w-10 text-right tabular-nums">{{ $item->persentase }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1 opacity-50 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('penerimaan.edit', $item) }}" title="Edit"
                                        class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('penerimaan.destroy', $item) }}"
                                        @submit.prevent="if(confirm('Yakin ingin menghapus data penerimaan ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 bg-slate-100 rounded-2xl">
                                        <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-600">Belum ada data penerimaan</p>
                                        <p class="text-xs text-slate-400 mt-1">Mulai tambahkan data penerimaan untuk melihat informasi di sini.</p>
                                    </div>
                                    <a href="{{ route('penerimaan.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                                        <x-heroicon-o-plus class="w-4 h-4"/>
                                        Tambah Penerimaan
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
            <p class="text-sm text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $penerimaans->count() }}</span> data penerimaan</p>
            @if(method_exists($penerimaans, 'links'))
                <div class="text-sm">
                    {{ $penerimaans->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-card>

    <div class="mt-6">
        <x-chart-card title="Grafik Penerimaan Bulanan" subtitle="Realisasi penerimaan per bulan tahun 2026">
            <div id="penerimaan-bar-chart" class="w-full min-h-[350px]"></div>
        </x-chart-card>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const barOptions = {
                series: [{
                    name: 'Realisasi',
                    data: [3.2, 3.8, 4.1, 3.9, 4.5, 4.2, 3.8]
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    fontFamily: 'Instrument Sans, sans-serif',
                    toolbar: { show: false }
                },
                colors: ['#22c55e'],
                plotOptions: {
                    bar: { borderRadius: 6, borderRadiusApplication: 'end', columnWidth: '60%' }
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
                    labels: { style: { fontSize: '11px', colors: '#94a3b8' } }
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '11px', colors: '#94a3b8' },
                        formatter: (val) => 'Rp ' + val.toFixed(1) + ' M'
                    }
                },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                dataLabels: { enabled: false }
            };
            new ApexCharts(document.querySelector('#penerimaan-bar-chart'), barOptions).render();
        });
    </script>
    @endpush
</x-app-layout>
