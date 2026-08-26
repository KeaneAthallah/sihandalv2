<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Sumber Dana" :breadcrumbs="['Data Master', 'Sumber Dana', 'Edit']">
            <x-slot name="actions">
                <x-secondary-button onclick="window.location='{{ route('sumber-dana.index') }}'">
                    <x-heroicon-o-arrow-left class="w-4 h-4"/>
                    Kembali
                </x-secondary-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('sumber-dana.update', $sumberDana) }}" method="POST">
            @csrf
            @method('PUT')

            <x-card title="Data Sumber Dana">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="opd_id" value="OPD" required/>
                        <select name="opd_id" id="opd_id" required
                            class="mt-1 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="" disabled>Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id', $sumberDana->opd_id) == $opd->id ? 'selected' : '' }}>
                                    {{ $opd->nama }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('opd_id')"/>
                    </div>

                    <div>
                        <x-input-label for="nama_sumber_dana" value="Nama Sumber Dana" required/>
                        <x-text-input type="text" name="nama_sumber_dana" id="nama_sumber_dana" class="mt-1" value="{{ old('nama_sumber_dana', $sumberDana->nama_sumber_dana) }}" placeholder="Masukkan nama sumber dana" required/>
                        <x-input-error :messages="$errors->get('nama_sumber_dana')"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="pagu" value="Pagu" required/>
                            <x-text-input type="number" name="pagu" id="pagu" class="mt-1" value="{{ old('pagu', $sumberDana->pagu) }}" placeholder="0" step="0.01" min="0" required/>
                            <x-input-error :messages="$errors->get('pagu')"/>
                        </div>
                        <div>
                            <x-input-label for="realisasi" value="Realisasi"/>
                            <x-text-input type="number" name="realisasi" id="realisasi" class="mt-1" value="{{ old('realisasi', $sumberDana->realisasi) }}" placeholder="0" step="0.01" min="0"/>
                            <x-input-error :messages="$errors->get('realisasi')"/>
                        </div>
                    </div>
                </div>
            </x-card>

            <div class="mt-5 flex items-center justify-end gap-3">
                <x-secondary-button type="button" onclick="window.location='{{ route('sumber-dana.index') }}'">Batal</x-secondary-button>
                <x-primary-button>Simpan Perubahan</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
