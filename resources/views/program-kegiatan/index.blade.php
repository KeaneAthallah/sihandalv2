<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Program & Kegiatan" :breadcrumbs="['Program & Kegiatan']">
            <x-slot name="actions">
                <a href="{{ route('program-kegiatan.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Program
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php $opds = \App\Models\Opd::orderBy('nama')->get(); @endphp

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Program" value="{{ $uniqueKegiatans->count() }}" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-list-bullet class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Kegiatan" value="{{ $programs->count() }}" color="info">
            <x-slot name="icon">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Anggaran Dialokasi" value="Rp {{ number_format($totalPagu / 1000000000, 1, ',', '.') }} M" color="success">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Realisasi" value="Rp {{ number_format($programs->sum('realisasi') / 1000000000, 1, ',', '.') }} M" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-arrow-trending-up class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <x-card :padding="false">
        <div class="px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="relative flex-1 lg:flex-none">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                    <input type="text" placeholder="Cari program atau kegiatan..." class="w-full lg:w-72 pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"/>
                </div>
                <select class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <option value="">Semua Tahun</option>
                    <option value="2026" selected>2026</option>
                    <option value="2025">2025</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="divide-y divide-slate-100">
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-14 text-left">No</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[110px] text-left">Kode</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">Program / Kegiatan</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[180px] text-left">OPD</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[120px] text-left">Sumber Dana</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[150px] text-right">Anggaran</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[90px] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($programs->take(20) as $idx => $prog)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-mono font-semibold text-primary whitespace-nowrap">{{ $prog->kode_kegiatan }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div>
                                    <span class="text-sm font-medium text-slate-800">{{ $prog->nama_kegiatan }}</span>
                                    @if($prog->nama_sub_kegiatan)
                                        <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[320px]">{{ $prog->nama_sub_kegiatan }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="text-sm text-slate-600 truncate block max-w-[180px]" title="{{ $prog->opd->nama ?? '-' }}">{{ $prog->opd->nama ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-medium text-slate-600 whitespace-nowrap">
                                    {{ $prog->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="text-sm font-semibold text-slate-700 whitespace-nowrap">
                                    Rp {{ number_format($prog->pagu, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('program-kegiatan.edit', $prog) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('program-kegiatan.destroy', $prog) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus program ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <p class="text-sm text-slate-500">Belum ada data program & kegiatan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ min(20, $programs->count()) }}</span> dari <span class="font-semibold text-slate-700">{{ $programs->count() }}</span> program & kegiatan</p>
        </div>
    </x-card>
</x-app-layout>
