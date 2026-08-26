<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Permintaan Dana" :breadcrumbs="['Permintaan Dana', 'Tambah']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <x-slot name="title">
                <span class="text-lg font-semibold text-slate-800">Form Tambah Permintaan Dana</span>
            </x-slot>

            <form action="{{ route('permintaan-dana.store') }}" method="POST">
                @csrf
                <div class="space-y-4" x-data="{
                    sumberDanas: {{ Js::from($sumberDanas->map(fn ($sd) => [
                        'id' => $sd->id,
                        'opd_id' => $sd->opd_id,
                        'label' => $sd->nama_sumber_dana,
                        'available' => $sd->availablePagu(),
                        'availableLabel' => 'Rp '.number_format($sd->availablePagu(), 0, ',', '.'),
                    ])) }},
                    opdId: {{ Js::from(old('opd_id')) }},
                    sumberDanaId: {{ Js::from(old('sumber_dana_id')) }},
                    filteredSumberDanas() {
                        return this.sumberDanas.filter(sd => sd.opd_id == this.opdId);
                    },
                    selectedAvailable: null,
                    onSumberChanged(e) {
                        const sd = this.sumberDanas.find(s => s.id == e.target.value);
                        this.selectedAvailable = sd ? sd : null;
                    }
                }">
                    <div>
                        <x-input-label>OPD <span class="text-red-500">*</span></x-input-label>
                        <select name="opd_id" x-model.number="opdId" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}">{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('opd_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label>Sumber Dana (OPD) <span class="text-red-500">*</span></x-input-label>
                        <select name="sumber_dana_id" x-model.number="sumberDanaId" @change="onSumberChanged($event)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                            <option value="">Pilih Sumber Dana</option>
                            <template x-for="sd in filteredSumberDanas()" :key="sd.id">
                                <option :value="sd.id" :disabled="sd.available <= 0" x-text="sd.label + ' (Sisa: ' + sd.availableLabel + ')'"></option>
                            </template>
                        </select>
                        <template x-if="selectedAvailable">
                            <p class="mt-1 text-xs text-emerald-600" x-text="'Sisa pagu tersedia: ' + selectedAvailable.availableLabel"></p>
                        </template>
                        <x-input-error :messages="$errors->get('sumber_dana_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label>Jumlah (Rp) <span class="text-red-500">*</span></x-input-label>
                        <x-text-input type="number" name="jumlah" :value="old('jumlah')" min="1" step="1" placeholder="0" required />
                        <x-input-error :messages="$errors->get('jumlah')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label>Keperluan <span class="text-red-500">*</span></x-input-label>
                        <x-text-input type="text" name="keperluan" :value="old('keperluan')" placeholder="Deskripsi keperluan..." required />
                        <x-input-error :messages="$errors->get('keperluan')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Tanggal" />
                        <x-text-input type="date" name="tanggal" :value="old('tanggal', date('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('tanggal')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Catatan" />
                        <textarea name="catatan" rows="3" placeholder="Catatan tambahan (opsional)..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">{{ old('catatan') }}</textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-1" />
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                    <a href="{{ route('permintaan-dana.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-xl font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-all">Batal</a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
