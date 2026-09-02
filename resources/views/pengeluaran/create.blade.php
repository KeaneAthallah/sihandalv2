<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Pengeluaran" :breadcrumbs="['Keuangan', 'Pengeluaran', 'Tambah Baru']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Form Pengeluaran Baru">
            <form action="{{ route('pengeluaran.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <x-input-label value="OPD" />
                        <select name="opd_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('opd_id')" />
                    </div>

                    <div>
                        <x-input-label value="Rekening" />
                        <select name="rekening_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Pilih Rekening (Opsional)</option>
                            @foreach($rekenings as $rek)
                                <option value="{{ $rek->id }}" {{ old('rekening_id') == $rek->id ? 'selected' : '' }}>{{ $rek->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('rekening_id')" />
                    </div>

                    <div>
                        <x-input-label value="Kegiatan" />
                        <select name="kegiatan_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Pilih Kegiatan</option>
                            @foreach($kegiatans as $kegiatan)
                                <option value="{{ $kegiatan->id }}" {{ old('kegiatan_id') == $kegiatan->id ? 'selected' : '' }}>{{ $kegiatan->kode_kegiatan . ' - ' . $kegiatan->nama_kegiatan }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('kegiatan_id')" />
                    </div>

                    <div>
                        <x-input-label value="Sumber Dana" />
                        <select name="sumber_dana_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                            <option value="">Pilih Sumber Dana</option>
                            @foreach($sumberDanas as $sd)
                                <option value="{{ $sd->id }}" {{ old('sumber_dana_id') == $sd->id ? 'selected' : '' }}>{{ $sd->nama_sumber_dana }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('sumber_dana_id')" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Anggaran" />
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                <x-text-input name="anggaran" type="number" :value="old('anggaran')" min="0" placeholder="0" class="pl-10" required />
                            </div>
                            <x-input-error :messages="$errors->get('anggaran')" />
                        </div>
                        <div>
                            <x-input-label value="Realisasi" />
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                <x-text-input name="realisasi" type="number" :value="old('realisasi')" min="0" placeholder="0" class="pl-10" />
                            </div>
                            <x-input-error :messages="$errors->get('realisasi')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Tanggal" />
                        <x-text-input name="tanggal" type="date" :value="old('tanggal')" />
                        <x-input-error :messages="$errors->get('tanggal')" />
                    </div>

                    <div>
                        <x-input-label value="Keterangan" />
                        <textarea name="keterangan" rows="3" placeholder="Tambahkan keterangan jika diperlukan..." class="input">{{ old('keterangan') }}</textarea>
                        <x-input-error :messages="$errors->get('keterangan')" />
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('pengeluaran.index') }}" class="btn-secondary">
                        Batal
                    </a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
