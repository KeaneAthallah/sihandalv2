<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Penerimaan" :breadcrumbs="['Keuangan', 'Penerimaan', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Edit Penerimaan">
            <form action="{{ route('penerimaan.update', $penerimaan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <x-input-label value="OPD" />
                        <select name="opd_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id', $penerimaan->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('opd_id')" />
                    </div>

                    <div>
                        <x-input-label value="Rekening" />
                        <select name="rekening_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Pilih Rekening (Opsional)</option>
                            @foreach($rekenings as $rekening)
                                <option value="{{ $rekening->id }}" {{ old('rekening_id', $penerimaan->rekening_id) == $rekening->id ? 'selected' : '' }}>{{ $rekening->kode . ' - ' . $rekening->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('rekening_id')" />
                    </div>

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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Target" />
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                <x-text-input name="target" type="number" :value="old('target', $penerimaan->target)" step="0.01" min="0" placeholder="0" class="pl-10" required />
                            </div>
                            <x-input-error :messages="$errors->get('target')" />
                        </div>
                        <div>
                            <x-input-label value="Realisasi" />
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                <x-text-input name="realisasi" type="number" :value="old('realisasi', $penerimaan->realisasi)" step="0.01" min="0" placeholder="0" class="pl-10" />
                            </div>
                            <x-input-error :messages="$errors->get('realisasi')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Tanggal" />
                        <x-text-input name="tanggal" type="date" :value="old('tanggal', $penerimaan->tanggal?->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('tanggal')" />
                    </div>

                    <div>
                        <x-input-label value="Keterangan" />
                        <textarea name="keterangan" rows="3" placeholder="Tambahkan keterangan jika diperlukan..." class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">{{ old('keterangan', $penerimaan->keterangan) }}</textarea>
                        <x-input-error :messages="$errors->get('keterangan')" />
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('penerimaan.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
