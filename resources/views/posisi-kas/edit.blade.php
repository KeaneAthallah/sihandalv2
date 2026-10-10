<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Posisi Kas" :breadcrumbs="['Posisi Kas', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Edit Posisi Kas">
            <form action="{{ route('posisi-kas.update', $posisiKas) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="opd_id" value="OPD" />
                    <select name="opd_id" id="opd_id" required class="w-full px-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="">Pilih OPD</option>
                        @foreach($opds as $opd)
                            <option value="{{ $opd->id }}" {{ old('opd_id', $posisiKas->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('opd_id')" />
                </div>

                <div>
                    <x-input-label for="tanggal" value="Tanggal" />
                    <input type="text" name="tanggal" id="tanggal" value="{{ old('tanggal', $posisiKas->tanggal?->format('Y-m-d')) }}" class="datepicker w-full px-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <x-input-error :messages="$errors->get('tanggal')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="nama_rekening" value="Nama Rekening" />
                        <input type="text" name="nama_rekening" id="nama_rekening" value="{{ old('nama_rekening', $posisiKas->nama_rekening) }}" required class="w-full px-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <x-input-error :messages="$errors->get('nama_rekening')" />
                    </div>
                    <div>
                        <x-input-label for="nomor_rekening" value="Nomor Rekening" />
                        <input type="text" name="nomor_rekening" id="nomor_rekening" value="{{ old('nomor_rekening', $posisiKas->nomor_rekening) }}" class="w-full px-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <x-input-error :messages="$errors->get('nomor_rekening')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="saldo" value="Saldo" />
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-content-muted font-medium">Rp</span>
                        <input type="number" name="saldo" id="saldo" step="0.01" value="{{ old('saldo', $posisiKas->saldo) }}" required class="w-full pl-10 pr-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    </div>
                    <x-input-error :messages="$errors->get('saldo')" />
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('posisi-kas.index') }}" class="btn-secondary">
                        Batal
                    </a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
