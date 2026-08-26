<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Program & Kegiatan" :breadcrumbs="['Program & Kegiatan']">
            <x-slot name="actions">
                <a href="{{ route('program-kegiatan.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Program
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php $opds = \App\Models\Opd::orderBy('nama')->get(); @endphp

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Total Program" value="{{ $uniqueKegiatans->count() }}" change="+2 baru" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-list-bullet class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Kegiatan" value="{{ $programs->count() }}" change="+8 baru" changeType="up" color="info">
            <x-slot name="icon">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Anggaran Dialokasi" value="Rp {{ number_format($totalPagu / 1000000000, 1, ',', '.') }} M" change="+5.3%" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Realisasi" value="Rp {{ number_format($programs->sum('realisasi') / 1000000000, 1, ',', '.') }} M" change="{{ $totalPagu > 0 ? round(($programs->sum('realisasi') / $totalPagu) * 100, 1) : 0 }}%" changeType="up" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-arrow-trending-up class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Filter Bar --}}
    <x-filter-bar>
        <div class="relative">
            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
            <input type="text" placeholder="Cari program atau kegiatan..." class="pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 w-full lg:w-72 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"/>
        </div>
        <select class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
            <option value="">Semua Tahun</option>
            <option value="2026" selected>2026</option>
            <option value="2025">2025</option>
        </select>
    </x-filter-bar>

    {{-- Data Table --}}
    <x-card>
        <div class="overflow-x-auto -mx-5 lg:-mx-6 px-5 lg:px-6">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50/70">
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Program / Kegiatan</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sumber Dana</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Anggaran</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($programs->take(20) as $idx => $prog)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-4 py-4">
                                <span class="text-xs font-mono font-semibold text-primary bg-primary/10 px-2.5 py-1 rounded-lg whitespace-nowrap">{{ $prog->kode_kegiatan }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="text-sm font-medium text-slate-800">{{ $prog->nama_kegiatan }}</span>
                                @if($prog->nama_sub_kegiatan)
                                    <div><span class="text-xs text-slate-400">{{ $prog->nama_sub_kegiatan }}</span></div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-600 max-w-[200px] truncate">{{ $prog->opd->nama ?? '-' }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200 whitespace-nowrap">
                                    {{ $prog->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-700 text-right whitespace-nowrap">
                                Rp {{ number_format($prog->pagu / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('program-kegiatan.edit', $prog) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('program-kegiatan.destroy', $prog) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus program ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                    <p class="text-sm text-slate-500">Belum ada data program & kegiatan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan {{ min(20, $programs->count()) }} dari {{ $programs->count() }} program & kegiatan</p>
        </div>
    </x-card>

</x-app-layout>
