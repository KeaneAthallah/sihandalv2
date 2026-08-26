<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Sumber Dana" :breadcrumbs="['Sumber Dana', 'Edit']">
            <x-slot name="actions">
                <x-secondary-button onclick="window.location='{{ route('sumber-dana.index') }}'">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-1.5"/>
                    Kembali
                </x-secondary-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <form action="{{ route('sumber-dana.update', $sumberDana) }}" method="POST">
            @csrf
            @method('PUT')

            <x-card>
                <x-slot name="header">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-50 rounded-xl">
                            <x-heroicon-o-pencil-square class="w-5 h-5 text-amber-600"/>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Data Sumber Dana</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Perbarui informasi sumber dana</p>
                        </div>
                    </div>
                </x-slot>

                <div class="space-y-5">
                    {{-- OPD --}}
                    <div>
                        <x-input-label for="opd_id" value="OPD" required/>
                        <select
                            name="opd_id"
                            id="opd_id"
                            required
                            class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                            <option value="" disabled>Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id', $sumberDana->opd_id) == $opd->id ? 'selected' : '' }}>
                                    {{ $opd->nama }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('opd_id')"/>
                    </div>

                    {{-- Nama Sumber Dana --}}
                    <div>
                        <x-input-label for="nama_sumber_dana" value="Nama Sumber Dana" required/>
                        <x-text-input
                            type="text"
                            name="nama_sumber_dana"
                            id="nama_sumber_dana"
                            class="mt-1.5"
                            value="{{ old('nama_sumber_dana', $sumberDana->nama_sumber_dana) }}"
                            placeholder="Masukkan nama sumber dana"
                            required/>
                        <x-input-error :messages="$errors->get('nama_sumber_dana')"/>
                    </div>

                    {{-- Financial Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="pagu" value="Pagu" required/>
                            <x-text-input
                                type="number"
                                name="pagu"
                                id="pagu"
                                class="mt-1.5"
                                value="{{ old('pagu', $sumberDana->pagu) }}"
                                placeholder="Masukkan pagu anggaran"
                                step="0.01"
                                min="0"
                                required/>
                            <x-input-error :messages="$errors->get('pagu')"/>
                        </div>
                        <div>
                            <x-input-label for="realisasi" value="Realisasi"/>
                            <x-text-input
                                type="number"
                                name="realisasi"
                                id="realisasi"
                                class="mt-1.5"
                                value="{{ old('realisasi', $sumberDana->realisasi) }}"
                                placeholder="Masukkan realisasi"
                                step="0.01"
                                min="0"/>
                            <x-input-error :messages="$errors->get('realisasi')"/>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Form Actions --}}
            <div class="mt-6 flex items-center justify-end gap-3">
                <x-secondary-button type="button" onclick="window.location='{{ route('sumber-dana.index') }}'">
                    Batal
                </x-secondary-button>
                <x-primary-button>
                    <x-heroicon-o-check class="w-4 h-4 mr-1.5"/>
                    Simpan Perubahan
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
