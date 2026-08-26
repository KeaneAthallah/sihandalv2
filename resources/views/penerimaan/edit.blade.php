<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Penerimaan" :breadcrumbs="['Keuangan', 'Penerimaan', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600">
                    <x-heroicon-o-pencil-square class="w-5 h-5"/>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">Edit Penerimaan</h2>
                    <p class="text-xs text-slate-400">Ubah data penerimaan yang telah terdaftar</p>
                </div>
            </div>

            <form action="{{ route('penerimaan.update', $penerimaan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-5">
                    <div class="pb-5 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Informasi OPD & Rekening</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <x-input-label value="OPD" />
                                <select name="opd_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all" required>
                                    <option value="">Pilih OPD</option>
                                    @foreach($opds as $opd)
                                        <option value="{{ $opd->id }}" {{ old('opd_id', $penerimaan->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('opd_id')" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label value="Rekening" />
                                <select name="rekening_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                                    <option value="">Pilih Rekening (Opsional)</option>
                                    @foreach($rekenings as $rekening)
                                        <option value="{{ $rekening->id }}" {{ old('rekening_id', $penerimaan->rekening_id) == $rekening->id ? 'selected' : '' }}>{{ $rekening->kode . ' - ' . $rekening->nama }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('rekening_id')" />
                            </div>
                        </div>
                    </div>

                    <div class="pb-5 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Sumber Dana</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Kode Sumber Dana" />
                                <x-text-input name="kode_sumber_dana" :value="old('kode_sumber_dana', $penerimaan->kode_sumber_dana)" placeholder="Masukkan kode sumber dana" />
                                <x-input-error :messages="$errors->get('kode_sumber_dana')" />
                            </div>
                            <div>
                                <x-input-label value="Nama Sumber Dana" />
                                <x-text-input name="nama_sumber_dana" :value="old('nama_sumber_dana', $penerimaan->nama_sumber_dana)" placeholder="Masukkan nama sumber dana" />
                                <x-input-error :messages="$errors->get('nama_sumber_dana')" />
                            </div>
                        </div>
                    </div>

                    <div class="pb-5 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Nilai & Tanggal</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Target" />
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                    <x-text-input name="target" type="number" :value="old('target', $penerimaan->target)" step="0.01" min="0" placeholder="0" class="pl-10" required />
                                </div>
                                <x-input-error :messages="$errors->get('target')" />
                            </div>
                            <div>
                                <x-input-label value="Realisasi" />
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                    <x-text-input name="realisasi" type="number" :value="old('realisasi', $penerimaan->realisasi)" step="0.01" min="0" placeholder="0" class="pl-10" />
                                </div>
                                <x-input-error :messages="$errors->get('realisasi')" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label value="Tanggal" />
                                <x-text-input name="tanggal" type="date" :value="old('tanggal', $penerimaan->tanggal?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('tanggal')" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Keterangan</h3>
                        <textarea name="keterangan" rows="3" placeholder="Tambahkan keterangan jika diperlukan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">{{ old('keterangan', $penerimaan->keterangan) }}</textarea>
                        <x-input-error :messages="$errors->get('keterangan')" />
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('penerimaan.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-300 rounded-xl font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                    <x-primary-button class="px-6 py-2.5">
                        <x-heroicon-o-check class="w-4 h-4 mr-1.5"/>
                        Simpan Perubahan
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
