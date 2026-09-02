<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Transaksi Penerimaan" :breadcrumbs="['Keuangan', 'Transaksi Penerimaan', 'Tambah Baru']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Form Transaksi Penerimaan Baru">
            <form action="{{ route('transaksi-penerimaan.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <x-input-label value="Penerimaan" />
                        <select name="penerimaan_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                            <option value="">Pilih Penerimaan</option>
                            @foreach($penerimaans as $p)
                                <option value="{{ $p->id }}" {{ old('penerimaan_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->sumberDana?->nama_sumber_dana ?? $p->nama_sumber_dana ?? '-' }} - {{ $p->opd?->nama ?? 'Provinsi' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('penerimaan_id')" />
                    </div>

                    <div>
                        <x-input-label value="Realisasi" />
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                            <x-text-input name="realisasi" type="number" :value="old('realisasi')" step="0.01" min="0" placeholder="0" class="pl-10" required />
                        </div>
                        <x-input-error :messages="$errors->get('realisasi')" />
                    </div>

                    <div>
                        <x-input-label value="Tanggal" />
                        <x-text-input name="tanggal" type="date" :value="old('tanggal', now()->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('tanggal')" />
                    </div>

                    <div>
                        <x-input-label value="Keterangan" />
                        <textarea name="keterangan" rows="3" placeholder="Tambahkan keterangan jika diperlukan..." class="input">{{ old('keterangan') }}</textarea>
                        <x-input-error :messages="$errors->get('keterangan')" />
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('transaksi-penerimaan.index') }}" class="btn-secondary">
                        Batal
                    </a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>