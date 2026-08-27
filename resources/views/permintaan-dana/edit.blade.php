<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Permintaan Dana" :breadcrumbs="['Transaksi', 'Permintaan Dana', 'Edit']">
            <x-slot name="actions">
                <a href="{{ route('permintaan-dana.index') }}"
                    class="btn-secondary">
                    Kembali
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">

        <div class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div>
                <h3 class="text-sm font-semibold text-amber-800">Mode Edit</h3>
                <p class="text-sm text-amber-700 mt-0.5">
                    Mengedit permintaan <span class="font-mono font-semibold">{{ $permintaanDana->nomor_permintaan }}</span>
                    @if($permintaanDana->status === 'ditolak')
                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">Ditolak</span>
                    @endif
                </p>
            </div>
        </div>

        <x-card title="Formulir Permintaan Dana">
            <form action="{{ route('permintaan-dana.update', $permintaanDana) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label>OPD <span class="text-red-500">*</span></x-input-label>
                            <select name="opd_id"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                                <option value="">Pilih OPD</option>
                                @foreach($opds as $opd)
                                    <option value="{{ $opd->id }}" {{ old('opd_id', $permintaanDana->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('opd_id')" class="mt-1"/>
                        </div>
                        <div>
                            <x-input-label>Sumber Dana <span class="text-red-500">*</span></x-input-label>
                            <select name="sumber_dana_id"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                                <option value="">Pilih Sumber Dana</option>
                                @foreach($sumberDanas as $sd)
                                    <option value="{{ $sd->id }}" {{ old('sumber_dana_id', $permintaanDana->sumber_dana_id) == $sd->id ? 'selected' : '' }}>{{ $sd->nama_sumber_dana }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('sumber_dana_id')" class="mt-1"/>
                        </div>
                    </div>

                    <div>
                        <x-input-label>Jumlah (Rp) <span class="text-red-500">*</span></x-input-label>
                        <x-text-input type="number" name="jumlah" :value="old('jumlah', $permintaanDana->jumlah)" min="1" step="1" placeholder="0" required/>
                        <x-input-error :messages="$errors->get('jumlah')" class="mt-1"/>
                    </div>

                    <div>
                        <x-input-label>Keperluan <span class="text-red-500">*</span></x-input-label>
                        <x-text-input type="text" name="keperluan" :value="old('keperluan', $permintaanDana->keperluan)" placeholder="Contoh: Pengadaan perlengkapan kantor" required/>
                        <x-input-error :messages="$errors->get('keperluan')" class="mt-1"/>
                    </div>

                    <div>
                        <x-input-label value="Tanggal"/>
                        <x-text-input type="date" name="tanggal" :value="old('tanggal', $permintaanDana->tanggal?->format('Y-m-d'))"/>
                        <x-input-error :messages="$errors->get('tanggal')" class="mt-1"/>
                    </div>

                    <div>
                        <x-input-label value="Catatan"/>
                        <textarea name="catatan" rows="3" placeholder="Catatan tambahan (opsional)..."
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition resize-none">{{ old('catatan', $permintaanDana->catatan) }}</textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-1"/>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('permintaan-dana.index') }}"
                           class="btn-secondary">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-dark transition">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
