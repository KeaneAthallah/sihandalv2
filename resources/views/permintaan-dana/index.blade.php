<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Permintaan Dana OPD" :breadcrumbs="['Permintaan Dana OPD']">
            <x-slot name="actions">
                <a href="{{ route('permintaan-dana.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Buat Permintaan
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    @if($errors->any())
        <x-alert type="danger" :dismissible="true">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Total Permintaan" value="Rp {{ number_format($totalPermintaan / 1000000000, 1, ',', '.') }} M" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Disetujui" value="Rp {{ number_format($totalDisetujui / 1000000000, 1, ',', '.') }} M" color="success">
            <x-slot name="icon">
                <x-heroicon-o-check-circle class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Menunggu" value="Rp {{ number_format($totalMenunggu / 1000000000, 1, ',', '.') }} M" change="{{ $permintaanDanas->where('status', 'menunggu')->count() }} permintaan" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Permintaan" value="{{ $permintaanDanas->count() }}" change="total" color="info">
            <x-slot name="icon">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <x-card>
        <div class="overflow-x-auto -mx-5 lg:-mx-6 px-5 lg:px-6">
            <table class="w-full text-sm min-w-[1000px]">
                <thead>
                    <tr class="bg-slate-50/70">
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">No Permintaan</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sumber Dana</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Keperluan</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jumlah</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($permintaanDanas as $idx => $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-4 py-4">
                                <span class="text-sm font-mono font-semibold text-primary">{{ $item->nomor_permintaan }}</span>
                            </td>
                            <td class="px-4 py-4 text-slate-700 max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200 whitespace-nowrap">
                                    {{ $item->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-600 max-w-[220px] truncate">{{ $item->keperluan }}</td>
                            <td class="px-4 py-4 font-semibold text-primary text-right whitespace-nowrap">
                                Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <x-status-badge :status="$item->status"/>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- View (all statuses) --}}
                                    <button
                                        x-data
                                        @click="
                                            $dispatch('open-view-permintaan', {
                                                nomor: '{{ $item->nomor_permintaan }}',
                                                opd: '{{ $item->opd->nama ?? "-" }}',
                                                sumber_dana: '{{ $item->sumber_dana }}',
                                                keperluan: '{{ $item->keperluan }}',
                                                jumlah: '{{ number_format($item->jumlah, 0, ',', '.') }}',
                                                status: '{{ $item->status }}',
                                                tanggal: '{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '-' }}',
                                                catatan: '{{ addslashes($item->catatan ?? '-') }}',
                                                created: '{{ $item->created_at->format('d M Y H:i') }}'
                                            });
                                            $dispatch('open-modal', 'view-permintaan-dana');
                                        "
                                        title="Lihat Detail"
                                        class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                                        <x-heroicon-o-eye class="w-4 h-4"/>
                                    </button>

                                    @if(in_array($item->status, ['draft', 'ditolak']))
                                        <a href="{{ route('permintaan-dana.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                            <x-heroicon-o-pencil class="w-4 h-4"/>
                                        </a>
                                    @endif

                                    {{-- Submit (draft only) --}}
                                    @if($item->status === 'draft')
                                        <form method="POST" action="{{ route('permintaan-dana.submit', $item) }}"
                                              x-data
                                              @submit.prevent="if(confirm('Yakin ingin mengajukan permintaan ini?')) $el.submit()">
                                            @csrf
                                            <button type="submit" title="Ajukan"
                                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                                                <x-heroicon-o-paper-airplane class="w-4 h-4"/>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Delete (draft / ditolak) --}}
                                    @if(in_array($item->status, ['draft', 'ditolak']))
                                        <form method="POST" action="{{ route('permintaan-dana.destroy', $item) }}"
                                              x-data
                                              @submit.prevent="if(confirm('Yakin ingin menghapus?')) $el.submit()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                                <x-heroicon-o-trash class="w-4 h-4"/>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                    <p class="text-sm text-slate-500">Belum ada permintaan dana</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan {{ $permintaanDanas->count() }} permintaan dana</p>
        </div>
    </x-card>

</x-app-layout>
