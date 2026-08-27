<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Program" :breadcrumbs="['Program & Kegiatan', 'Edit']" />
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-5">
        <form action="{{ route('program-kegiatan.update', $program) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-card title="Program">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kode_program" value="Kode Program" />
                            <x-text-input type="text" name="kode_program" id="kode_program" value="{{ old('kode_program', $program->kode_program) }}" placeholder="Misal: 1.1" required class="mt-1.5" />
                            <x-input-error :messages="$errors->get('kode_program')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="nama_program" value="Nama Program" />
                            <x-text-input type="text" name="nama_program" id="nama_program" value="{{ old('nama_program', $program->nama_program) }}" placeholder="Masukkan nama program" required class="mt-1.5" />
                            <x-input-error :messages="$errors->get('nama_program')" class="mt-1" />
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end">
                    <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                        Simpan Perubahan Program
                    </button>
                </div>
            </x-card>
        </form>

        <x-card :padding="false" x-data="{ activeForm: null }">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <h3 class="card-title">Kegiatan</h3>
                <button type="button" @click="activeForm = activeForm === 'add' ? null : 'add'"
                    class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Kegiatan
                </button>
            </div>

            {{-- Inline Add Form --}}
            <div x-show="activeForm === 'add'" x-cloak class="border-b border-slate-100 bg-slate-50/60">
                <div class="px-5 py-4">
                    <p class="text-sm font-semibold text-slate-700 mb-3">Tambah Kegiatan Baru</p>
                    <form action="{{ route('program-kegiatan.kegiatan.store', $program) }}" method="POST">
                        @csrf
                        @include('program-kegiatan._form-kegiatan', ['idPrefix' => 'add', 'kegiatan' => null])

                        <div class="mt-4 flex items-center justify-end gap-3">
                            <button type="button" @click="activeForm = null" class="btn-secondary">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">Simpan Kegiatan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="divide-y divide-slate-100">
                            <th class="px-5 py-3 table-head w-14 text-left">No</th>
                            <th class="px-5 py-3 table-head text-left">Kegiatan</th>
                            <th class="px-5 py-3 table-head w-[150px] text-left">OPD</th>
                            <th class="px-5 py-3 table-head w-[120px] text-left">Sumber Dana</th>
                            <th class="px-5 py-3 table-head w-[140px] text-right">Pagu</th>
                            <th class="px-5 py-3 table-head w-[140px] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($kegiatans as $idx => $kegiatan)
                            <tr class="table-row">
                                <td class="px-5 py-3.5 text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="text-xs font-mono font-semibold text-primary">{{ $kegiatan->kode_kegiatan }}</span>
                                    <p class="text-sm font-medium text-slate-800">{{ $kegiatan->nama_kegiatan }}</p>
                                    @if($kegiatan->nama_sub_kegiatan)
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $kegiatan->nama_sub_kegiatan }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-600">{{ $kegiatan->opd?->nama ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-xs font-medium text-slate-600">{{ $kegiatan->sumberDana?->nama_sumber_dana ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap font-semibold text-slate-700">Rp {{ number_format($kegiatan->pagu, 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" @click="activeForm = activeForm === {{ $kegiatan->id }} ? null : {{ $kegiatan->id }}"
                                            class="icon-btn hover:text-amber-600 hover:bg-amber-50" title="Edit">
                                            <x-heroicon-o-pencil class="w-4 h-4"/>
                                        </button>
                                        <form method="POST" action="{{ route('program-kegiatan.kegiatan.destroy', [$program, $kegiatan]) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus kegiatan ini?')) $el.submit()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" class="icon-btn hover:text-red-600 hover:bg-red-50">
                                                <x-heroicon-o-trash class="w-4 h-4"/>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            {{-- Inline Edit Form --}}
                            <tr x-cloak x-show="activeForm === {{ $kegiatan->id }}">
                                <td colspan="6" class="px-5 py-4 bg-slate-50/60">
                                    <p class="text-sm font-semibold text-slate-700 mb-3">Edit Kegiatan: {{ $kegiatan->nama_kegiatan }}</p>
                                    <form action="{{ route('program-kegiatan.kegiatan.update', [$program, $kegiatan]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        @include('program-kegiatan._form-kegiatan', ['idPrefix' => 'edit_'.$kegiatan->id, 'kegiatan' => $kegiatan])

                                        <div class="mt-4 flex items-center justify-end gap-3">
                                            <button type="button" @click="activeForm = null" class="btn-secondary">Batal</button>
                                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <div class="empty-icon">
                                            <x-heroicon-o-clipboard-document-list class="w-7 h-7"/>
                                        </div>
                                        <p class="empty-title">Belum ada kegiatan</p>
                                        <p class="empty-desc">Klik "Tambah Kegiatan" untuk menambahkan kegiatan pada program ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>
