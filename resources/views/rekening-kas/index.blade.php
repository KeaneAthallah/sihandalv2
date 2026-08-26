<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Rekening Kas" :breadcrumbs="['Rekening Kas']">
            <x-slot name="actions">
                <a href="{{ route('rekening-kas.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Rekening
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Total Rekening" value="{{ $rekenings->count() }}" change="+2 baru" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-wallet class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Rekening Aktif" value="{{ $rekenings->where('tipe', 'kas')->count() }}" change="aktif" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-check-circle class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Saldo Total" value="Rp {{ number_format($totalKas / 1000000000, 1, ',', '.') }} M" change="+3.1%" changeType="up" color="info">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Semua Rekening" value="Rp {{ number_format($totalSaldo / 1000000000, 1, ',', '.') }} M" change="+15%" changeType="up" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-arrow-path class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <x-card>
        <div class="overflow-x-auto -mx-5 lg:-mx-6 px-5 lg:px-6">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="w-16 text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="w-[120px] text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Rekening</th>
                        <th class="w-[120px] text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipe</th>
                        <th class="w-[160px] text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Saldo</th>
                        <th class="w-[100px] text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekenings as $idx => $rek)
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <td class="px-4 py-3.5 text-slate-400 font-medium text-xs">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-mono font-semibold rounded-lg">
                                    {{ $rek->kode }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 group-hover:bg-primary/15 transition-colors">
                                        <x-heroicon-o-wallet class="w-4 h-4 text-primary"/>
                                    </div>
                                    <span class="text-sm font-medium text-slate-800">{{ $rek->nama }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-lg inline-flex items-center gap-1.5
                                    {{ $rek->tipe === 'kas' ? 'bg-blue-50 text-blue-600' : ($rek->tipe === 'pendapatan' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600') }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ ucfirst($rek->tipe) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="text-sm font-semibold text-slate-700 whitespace-nowrap">
                                    Rp {{ number_format($rek->saldo, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('rekening-kas.edit', $rek) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('rekening-kas.destroy', $rek) }}" @submit.prevent="if(confirm('Yakin ingin menghapus rekening {{ $rek->nama }}?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <x-heroicon-o-wallet class="w-7 h-7 text-slate-400"/>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm font-medium text-slate-600">Belum ada data rekening</p>
                                        <p class="text-xs text-slate-400 mt-1">Mulai tambahkan rekening kas untuk mengelola keuangan Anda.</p>
                                    </div>
                                    <a href="{{ route('rekening-kas.create') }}" class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:text-primary-dark transition-colors">
                                        <x-heroicon-o-plus class="w-4 h-4"/>
                                        Tambah Rekening
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $rekenings->count() }}</span> rekening</p>
        </div>
    </x-card>

</x-app-layout>
