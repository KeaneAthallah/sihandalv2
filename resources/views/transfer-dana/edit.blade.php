<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Transfer Dana" :breadcrumbs="['Transfer Dana', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <div class="mb-6 pb-5 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">Form Edit Transfer Dana</h2>
                <p class="text-sm text-slate-400 mt-0.5">Ubah data transfer dana sesuai kebutuhan</p>
            </div>

            <form action="{{ route('transfer-dana.update', $transferDana) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="opd_id" value="OPD" />
                    <select name="opd_id" id="opd_id" required class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                        <option value="">Pilih OPD</option>
                        @foreach($opds as $opd)
                            <option value="{{ $opd->id }}" {{ old('opd_id', $transferDana->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('opd_id')" class="mt-1.5" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="jumlah" value="Jumlah *" />
                        <div class="relative mt-1.5">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                            <input type="number" name="jumlah" id="jumlah" min="0" value="{{ old('jumlah', $transferDana->jumlah) }}" placeholder="0" required class="w-full pl-12 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                        </div>
                        <x-input-error :messages="$errors->get('jumlah')" class="mt-1.5" />
                    </div>
                    <div>
                        <x-input-label for="tanggal" value="Tanggal" />
                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', $transferDana->tanggal?->format('Y-m-d')) }}" class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                        <x-input-error :messages="$errors->get('tanggal')" class="mt-1.5" />
                    </div>
                </div>

                <div>
                    <x-input-label for="sumber_dana" value="Sumber Dana" />
                    <input type="text" name="sumber_dana" id="sumber_dana" value="{{ old('sumber_dana', $transferDana->sumber_dana) }}" placeholder="Masukkan sumber dana" required class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                    <x-input-error :messages="$errors->get('sumber_dana')" class="mt-1.5" />
                </div>

                <div>
                    <x-input-label for="status" value="Status" />
                    <select name="status" id="status" class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                        <option value="">Pilih Status</option>
                        @foreach(['draft' => 'Draft', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'gagal' => 'Gagal'] as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $transferDana->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1.5" />
                </div>

                <div>
                    <x-input-label for="keterangan" value="Keterangan" />
                    <textarea name="keterangan" id="keterangan" rows="3" placeholder="Tulis keterangan jika diperlukan" class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all resize-none">{{ old('keterangan', $transferDana->keterangan) }}</textarea>
                    <x-input-error :messages="$errors->get('keterangan')" class="mt-1.5" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                    <a href="{{ route('transfer-dana.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-xl font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                    <x-primary-button>
                        <x-heroicon-o-check class="w-4 h-4 mr-1.5"/>
                        Simpan Perubahan
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
