<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Pengeluaran" :breadcrumbs="['Keuangan', 'Pengeluaran', 'Tambah Baru']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 rounded-xl bg-red-50 text-red-600">
                    <x-heroicon-o-arrow-up-right class="w-5 h-5"/>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">Form Pengeluaran Baru</h2>
                    <p class="text-xs text-slate-400">Lengkapi data berikut untuk menambahkan pengeluaran</p>
                </div>
            </div>

            <form action="{{ route('pengeluaran.store') }}" method="POST">
                @csrf

                <div class="space-y-5">
                    <div class="pb-5 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Informasi OPD & Rekening</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <x-input-label value="OPD" />
                                <select name="opd_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all" required>
                                    <option value="">Pilih OPD</option>
                                    @foreach($opds as $opd)
                                        <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('opd_id')" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label value="Rekening" />
                                <select name="rekening_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                                    <option value="">Pilih Rekening (Opsional)</option>
                                    @foreach($rekenings as $rek)
                                        <option value="{{ $rek->id }}" {{ old('rekening_id') == $rek->id ? 'selected' : '' }}>{{ $rek->nama }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('rekening_id')" />
                            </div>
                        </div>
                    </div>

                    <div class="pb-5 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Kegiatan & Sumber Dana</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Kode Kegiatan" />
                                <x-text-input name="kode_kegiatan" :value="old('kode_kegiatan')" placeholder="Masukkan kode kegiatan" />
                                <x-input-error :messages="$errors->get('kode_kegiatan')" />
                            </div>
                            <div>
                                <x-input-label value="Nama Kegiatan" />
                                <x-text-input name="nama_kegiatan" :value="old('nama_kegiatan')" placeholder="Masukkan nama kegiatan" />
                                <x-input-error :messages="$errors->get('nama_kegiatan')" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label value="Sumber Dana" />
                                <x-text-input name="sumber_dana" :value="old('sumber_dana')" placeholder="Masukkan sumber dana" required />
                                <x-input-error :messages="$errors->get('sumber_dana')" />
                            </div>
                        </div>
                    </div>

                    <div class="pb-5 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Nilai & Tanggal</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Anggaran" />
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                    <x-text-input name="anggaran" type="number" :value="old('anggaran')" min="0" placeholder="0" class="pl-10" required />
                                </div>
                                <x-input-error :messages="$errors->get('anggaran')" />
                            </div>
                            <div>
                                <x-input-label value="Realisasi" />
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                    <x-text-input name="realisasi" type="number" :value="old('realisasi')" min="0" placeholder="0" class="pl-10" />
                                </div>
                                <x-input-error :messages="$errors->get('realisasi')" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label value="Tanggal" />
                                <x-text-input name="tanggal" type="date" :value="old('tanggal')" />
                                <x-input-error :messages="$errors->get('tanggal')" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Keterangan</h3>
                        <textarea name="keterangan" rows="3" placeholder="Tambahkan keterangan jika diperlukan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">{{ old('keterangan') }}</textarea>
                        <x-input-error :messages="$errors->get('keterangan')" />
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('pengeluaran.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-300 rounded-xl font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                    <x-primary-button class="px-6 py-2.5">
                        <x-heroicon-o-check class="w-4 h-4 mr-1.5"/>
                        Simpan Pengeluaran
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
