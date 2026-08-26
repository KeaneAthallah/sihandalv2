<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Rekening Kas" :breadcrumbs="['Rekening Kas']">
            <x-slot name="actions">
                <a href="{{ route('rekening-kas.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Rekening
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Rekening" value="{{ $rekenings->count() }}" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-wallet class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Rekening Aktif" value="{{ $rekenings->where('tipe', 'kas')->count() }}" color="success">
            <x-slot name="icon">
                <x-heroicon-o-check-circle class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Saldo Total" value="Rp {{ number_format($totalKas / 1000000000, 1, ',', '.') }} M" color="info">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Semua Rekening" value="Rp {{ number_format($totalSaldo / 1000000000, 1, ',', '.') }} M" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-arrow-path class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="divide-y divide-slate-100">
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-16 text-left">No</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[120px] text-left">Kode</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">Nama Rekening</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[120px] text-left">Tipe</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[160px] text-right">Saldo</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[100px] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekenings as $idx => $rek)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs font-semibold text-slate-600">{{ $rek->kode }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="text-sm font-medium text-slate-800">{{ $rek->nama }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-lg inline-flex items-center gap-1.5
                                    {{ $rek->tipe === 'kas' ? 'bg-blue-50 text-blue-600' : ($rek->tipe === 'pendapatan' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600') }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ ucfirst($rek->tipe) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="text-sm font-semibold text-slate-700 whitespace-nowrap">
                                    Rp {{ number_format($rek->saldo, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('rekening-kas.edit', $rek) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('rekening-kas.destroy', $rek) }}" @submit.prevent="if(confirm('Yakin ingin menghapus rekening {{ $rek->nama }}?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition" title="Hapus">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <p class="text-sm text-slate-500">Belum ada data rekening</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $rekenings->count() }}</span> rekening</p>
        </div>
    </x-card>
</x-app-layout>
