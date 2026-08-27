<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Transfer Dana" :breadcrumbs="['Transfer Dana', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Edit Transfer Dana">
            <form action="{{ route('transfer-dana.update', $transferDana) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="opd_id" value="OPD" />
                    <select name="opd_id" id="opd_id" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="">Pilih OPD</option>
                        @foreach($opds as $opd)
                            <option value="{{ $opd->id }}" {{ old('opd_id', $transferDana->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('opd_id')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="jumlah" value="Jumlah" />
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                            <input type="number" name="jumlah" id="jumlah" min="0" value="{{ old('jumlah', $transferDana->jumlah) }}" placeholder="0" required class="input pl-10 pr-3">
                        </div>
                        <x-input-error :messages="$errors->get('jumlah')" />
                    </div>
                    <div>
                        <x-input-label for="tanggal" value="Tanggal" />
                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', $transferDana->tanggal?->format('Y-m-d')) }}" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <x-input-error :messages="$errors->get('tanggal')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="sumber_dana" value="Sumber Dana" />
                    <input type="text" name="sumber_dana" id="sumber_dana" value="{{ old('sumber_dana', $transferDana->sumber_dana) }}" placeholder="Masukkan sumber dana" required class="input">
                    <x-input-error :messages="$errors->get('sumber_dana')" />
                </div>

                <div>
                    <x-input-label for="status" value="Status" />
                    <select name="status" id="status" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="">Pilih Status</option>
                        @foreach(['draft' => 'Draft', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'gagal' => 'Gagal'] as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $transferDana->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" />
                </div>

                <div>
                    <x-input-label for="keterangan" value="Keterangan" />
                    <textarea name="keterangan" id="keterangan" rows="3" placeholder="Tulis keterangan jika diperlukan" class="input">{{ old('keterangan', $transferDana->keterangan) }}</textarea>
                    <x-input-error :messages="$errors->get('keterangan')" />
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('transfer-dana.index') }}" class="btn-secondary">
                        Batal
                    </a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
