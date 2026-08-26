<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tahun Anggaran" :breadcrumbs="['Tahun Anggaran']">
            <x-slot name="actions">
                <button @click="$dispatch('open-modal', { name: 'add-tahun-anggaran' })"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Tahun Anggaran
                </button>
            </x-slot>
        </x-page-header>
    </x-slot>

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

    <x-card padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tahun</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Periode</th>
                        <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aktif</th>
                        <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($tahunAnggarans as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors {{ $item->is_active ? 'bg-primary/[0.03]' : '' }}">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $item->is_active ? 'bg-primary/10 text-primary' : 'bg-slate-100 text-slate-400' }}">
                                        <x-heroicon-o-calendar class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $item->tahun }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">Tahun Anggaran</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm text-slate-600">{{ $item->tanggal_mulai->format('d M Y') }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">s/d {{ $item->tanggal_selesai->format('d M Y') }}</p>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($item->status === 'open')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Terbuka
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Tertutup
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($item->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                                        <x-heroicon-s-star class="w-3 h-3" />
                                        Aktif
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('tahun-anggaran.activate', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 hover:bg-primary/10 hover:text-primary transition-colors">
                                            <x-heroicon-o-arrow-right class="w-3 h-3" />
                                            Aktifkan
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <form method="POST" action="{{ route('tahun-anggaran.update', $item) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $item->status === 'open' ? 'closed' : 'open' }}">
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors {{ $item->status === 'open' ? 'text-red-600 hover:bg-red-50' : 'text-emerald-600 hover:bg-emerald-50' }}">
                                            @if($item->status === 'open')
                                                <x-heroicon-o-lock-closed class="w-3.5 h-3.5" />
                                                Tutup
                                            @else
                                                <x-heroicon-o-lock-open class="w-3.5 h-3.5" />
                                                Buka
                                            @endif
                                        </button>
                                    </form>
                                    @unless($item->is_active)
                                        <span class="text-slate-200">|</span>
                                        <form method="POST" action="{{ route('tahun-anggaran.destroy', $item) }}"
                                              x-data
                                              @submit.prevent="if(confirm('Yakin ingin menghapus tahun anggaran {{ $item->tahun }}?')) $el.submit()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                                                <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                                Hapus
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100">
                                        <x-heroicon-o-calendar class="h-7 w-7 text-slate-300"/>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">Belum ada tahun anggaran</p>
                                        <p class="text-xs text-slate-400 mt-1">Klik "Tambah Tahun Anggaran" untuk membuat baru</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <x-modal name="add-tahun-anggaran" max-width="md">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                    <x-heroicon-o-plus class="w-5 h-5 text-primary" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Tambah Tahun Anggaran</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Buat periode anggaran baru</p>
                </div>
            </div>
            <form method="POST" action="{{ route('tahun-anggaran.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <x-input-label value="Tahun" />
                        <x-text-input type="text" name="tahun" maxlength="4" pattern="[0-9]{4}" placeholder="2026" class="mt-1.5" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Tanggal Mulai" />
                            <x-text-input type="date" name="tanggal_mulai" class="mt-1.5" required />
                        </div>
                        <div>
                            <x-input-label value="Tanggal Selesai" />
                            <x-text-input type="date" name="tanggal_selesai" class="mt-1.5" required />
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                    <x-secondary-button @click="$dispatch('close-modal', { name: 'add-tahun-anggaran' })">Batal</x-secondary-button>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
