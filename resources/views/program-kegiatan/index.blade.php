<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Program & Kegiatan" :breadcrumbs="['Program & Kegiatan']">
            <x-slot name="actions">
                <a href="{{ route('program-kegiatan.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Program
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Program" value="{{ $programs->count() }}" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-list-bullet class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Kegiatan" value="{{ $kegiatans->count() }}" color="info">
            <x-slot name="icon">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Anggaran Dialokasi" value="Rp {{ number_format($totalPagu / 1000000000, 1, ',', '.') }} M" color="success">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Realisasi" value="Rp {{ number_format($kegiatans->sum('realisasi') / 1000000000, 1, ',', '.') }} M" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-arrow-trending-up class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <x-card :padding="false">
        <div class="px-5 py-4 border-b border-slate-100">
            <div class="relative flex-1 lg:flex-none">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                <input type="text" placeholder="Cari program atau kegiatan..." class="input pl-9 lg:!w-72"/>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="divide-y divide-slate-100">
                        <th class="px-5 py-3 table-head w-14 text-left">No</th>
                        <th class="px-5 py-3 table-head text-left">Program / Kegiatan</th>
                        <th class="px-5 py-3 table-head w-[150px] text-left">OPD</th>
                        <th class="px-5 py-3 table-head w-[120px] text-left">Sumber Dana</th>
                        <th class="px-5 py-3 table-head w-[150px] text-right">Anggaran</th>
                        <th class="px-5 py-3 table-head w-[90px] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kegiatans->groupBy('program_id')->take(20) as $programId => $group)
                        @php($program = $programs->firstWhere('id', $programId))
                        <tr class="bg-slate-50/80">
                            <td class="px-5 py-3"></td>
                            <td colspan="5" class="px-5 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="text-xs font-mono font-bold text-primary whitespace-nowrap">{{ $program?->kode_program }}</span>
                                        <span class="text-sm font-semibold text-slate-800 truncate">{{ $program?->nama_program }}</span>
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary/10 text-primary whitespace-nowrap">{{ $group->count() }} kegiatan</span>
                                    </div>
                                    <div class="flex items-center justify-center gap-1 shrink-0">
                                        <a href="{{ route('program-kegiatan.edit', $program) }}" class="icon-btn hover:text-amber-600 hover:bg-amber-50" title="Edit program">
                                            <x-heroicon-o-pencil class="w-4 h-4"/>
                                        </a>
                                        <form method="POST" action="{{ route('program-kegiatan.destroy', $program) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus program ini beserta seluruh kegiatannya?')) $el.submit()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus program" class="icon-btn hover:text-red-600 hover:bg-red-50">
                                                <x-heroicon-o-trash class="w-4 h-4"/>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @foreach($group->take(50) as $idx => $kegiatan)
                            <tr class="table-row">
                                <td class="px-5 py-3.5 text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3.5 pl-10">
                                    <span class="text-xs font-mono font-semibold text-primary whitespace-nowrap">{{ $kegiatan->kode_kegiatan }}</span>
                                    <p class="text-sm font-medium text-slate-800">{{ $kegiatan->nama_kegiatan }}</p>
                                    @if($kegiatan->nama_sub_kegiatan)
                                        <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[320px]">{{ $kegiatan->nama_sub_kegiatan }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-sm text-slate-600 truncate block max-w-[180px]" title="{{ $kegiatan->opd->nama ?? '-' }}">{{ $kegiatan->opd->nama ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-xs font-medium text-slate-600 whitespace-nowrap">
                                        {{ $kegiatan->sumberDana?->nama_sumber_dana ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-sm font-semibold text-slate-700 whitespace-nowrap">
                                        Rp {{ number_format($kegiatan->pagu, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('program-kegiatan.edit', $kegiatan->program) }}" class="icon-btn hover:text-amber-600 hover:bg-amber-50" title="Edit">
                                            <x-heroicon-o-pencil class="w-4 h-4"/>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-list-bullet class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada data program & kegiatan</p>
                                    <p class="empty-desc">Program dan kegiatan akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $programs->count() }}</span> program / <span class="font-semibold text-slate-700">{{ $kegiatans->count() }}</span> kegiatan</p>
        </div>
    </x-card>
</x-app-layout>
