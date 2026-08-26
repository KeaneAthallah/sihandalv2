<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pengeluaran" :breadcrumbs="['Keuangan', 'Pengeluaran']">
            <x-slot name="actions">
                <a href="{{ route('pengeluaran.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Pengeluaran
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Total Pengeluaran" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" change="+8.2% dari bulan lalu" changeType="up" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Anggaran" value="Rp {{ number_format($totalAnggaran / 1000000000, 1, ',', '.') }} M" change="+3.1% dari bulan lalu" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-calendar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Persentase Realisasi" value="{{ $persentase }}%" change="Dari total anggaran" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Sisa Anggaran" value="Rp {{ number_format(($totalAnggaran - $totalRealisasi) / 1000000000, 1, ',', '.') }} M" change="Belum direalisasi" changeType="down" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 mb-6">
        <div class="flex items-center gap-3 flex-wrap" x-data="{ active: 'all' }">
            @php
                $chips = [
                    ['key' => 'all', 'label' => 'Semua'],
                    ['key' => 'realisasi', 'label' => 'Realisasi'],
                    ['key' => 'pending', 'label' => 'Pending'],
                ];
            @endphp
            @foreach($chips as $chip)
                <button
                    @click="active = '{{ $chip['key'] }}'"
                    :class="active === '{{ $chip['key'] }}' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 text-sm font-medium rounded-xl transition-all">
                    {{ $chip['label'] }}
                </button>
            @endforeach

            <div class="ml-auto flex items-center gap-2 flex-wrap">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                    <input type="text" placeholder="Cari SP2D, OPD..."
                        class="pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 w-48 lg:w-56 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"/>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <div class="xl:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Daftar Pengeluaran</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Data realisasi pengeluaran anggaran</p>
                    </div>
                </x-slot>

                <div class="overflow-x-auto -mx-5 lg:-mx-6">
                    <table class="w-full text-sm min-w-[950px]">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[50px]">No</th>
                                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[120px]">Tanggal</th>
                                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kegiatan</th>
                                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">OPD</th>
                                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[120px]">Sumber Dana</th>
                                <th class="text-right px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[130px]">Anggaran</th>
                                <th class="text-right px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[130px]">Realisasi</th>
                                <th class="text-center px-6 py-3.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-[100px]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($pengeluarans as $idx => $item)
                                <tr class="hover:bg-slate-50/60 transition-colors group">
                                    <td class="px-6 py-4 text-slate-400 font-medium tabular-nums">{{ $idx + 1 }}</td>
                                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-slate-800">{{ $item->nama_kegiatan ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-700 font-medium max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 whitespace-nowrap">
                                            {{ $item->sumber_dana }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-700 text-right whitespace-nowrap tabular-nums">
                                        Rp {{ number_format($item->anggaran / 1000000000, 1, ',', '.') }} M
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-red-500 text-right whitespace-nowrap tabular-nums">
                                        Rp {{ number_format($item->realisasi / 1000000000, 1, ',', '.') }} M
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1 opacity-50 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('pengeluaran.edit', $item) }}" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                                <x-heroicon-o-pencil class="w-4 h-4"/>
                                            </a>
                                            <form method="POST" action="{{ route('pengeluaran.destroy', $item) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus pengeluaran ini?')) $el.submit()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
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
                                                <p class="text-sm font-semibold text-slate-600">Belum ada data pengeluaran</p>
                                                <p class="text-xs text-slate-400 mt-1">Mulai tambahkan data pengeluaran untuk melihat informasi di sini.</p>
                                            </div>
                                            <a href="{{ route('pengeluaran.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                                                <x-heroicon-o-plus class="w-4 h-4"/>
                                                Tambah Pengeluaran
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-sm text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $pengeluarans->count() }}</span> data pengeluaran</p>
                    @if(method_exists($pengeluarans, 'links'))
                        <div class="text-sm">
                            {{ $pengeluarans->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card>
                <div class="p-5 lg:p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
                            <x-heroicon-o-document-chart-bar class="w-5 h-5"/>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Ringkasan Anggaran</h3>
                            <p class="text-xs text-slate-400">Status realisasi pengeluaran</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <div class="flex items-center gap-2.5">
                                <div class="w-2 h-2 rounded-full bg-primary"></div>
                                <span class="text-sm text-slate-600">Total Anggaran</span>
                            </div>
                            <span class="text-sm font-bold text-slate-800 tabular-nums">Rp {{ number_format($totalAnggaran / 1000000000, 1, ',', '.') }} M</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <div class="flex items-center gap-2.5">
                                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                <span class="text-sm text-slate-600">Total Realisasi</span>
                            </div>
                            <span class="text-sm font-bold text-red-500 tabular-nums">Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <div class="flex items-center gap-2.5">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                <span class="text-sm text-slate-600">Persentase</span>
                            </div>
                            <span class="text-sm font-bold text-primary tabular-nums">{{ $persentase }}%</span>
                        </div>
                    </div>

                    <div class="mt-5 pt-5 border-t border-slate-100">
                        <x-progress-card title="Progres Realisasi" :percentage="min(round($persentase), 100)" color="info" />
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-5 lg:p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600">
                            <x-heroicon-o-light-bulb class="w-5 h-5"/>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-800">Insight</h3>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Realisasi pengeluaran saat ini berada di angka <span class="font-bold text-primary">{{ $persentase }}%</span> dari total anggaran. 
                        @if($persentase > 80)
                            Anggaran sudah terpakai cukup besar, pastikan penggunaan selanjutnya sesuai prioritas.
                        @elseif($persentase > 50)
                            Penggunaan anggaran berjalan normal sesuai target.
                        @else
                            Masih ada sisa anggaran yang cukup untuk realisasi kegiatan.
                        @endif
                    </p>
                </div>
            </x-card>
        </div>
    </div>

    <x-chart-card title="Pengeluaran Bulanan" subtitle="Realisasi pengeluaran per bulan tahun 2026">
        <div id="monthly-chart" class="w-full min-h-[350px]"></div>
    </x-chart-card>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const barOptions = {
                series: [{
                    name: 'Realisasi',
                    data: [2.1, 2.4, 2.8, 2.5, 3.0, 2.7, 2.6]
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    fontFamily: 'Instrument Sans, sans-serif',
                    toolbar: { show: false }
                },
                colors: ['#0F4C81'],
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        borderRadiusApplication: 'end',
                        horizontal: false,
                        columnWidth: '55%',
                    }
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
                    labels: { style: { fontSize: '12px', colors: '#94a3b8' } }
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '11px', colors: '#94a3b8' },
                        formatter: (v) => 'Rp ' + v + ' M'
                    }
                },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                dataLabels: { enabled: false }
            };
            new ApexCharts(document.querySelector('#monthly-chart'), barOptions).render();
        });
    </script>
    @endpush
</x-app-layout>
