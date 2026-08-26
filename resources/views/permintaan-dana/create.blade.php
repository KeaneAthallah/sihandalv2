<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Buat Permintaan Dana" :breadcrumbs="['Keuangan', 'Permintaan Dana', 'Buat Baru']">
            <x-slot name="actions">
                <a href="{{ route('permintaan-dana.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                    <x-heroicon-o-arrow-left class="w-4 h-4"/>
                    Kembali
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Financial Context Card --}}
        <div class="bg-gradient-to-r from-primary/5 to-primary/10 rounded-2xl border border-primary/10 p-5">
            <div class="flex items-start gap-3">
                <div class="p-2.5 bg-primary/10 rounded-xl shrink-0">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-primary"/>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Informasi Penting</h3>
                    <p class="text-sm text-slate-600 mt-1">Setelah permintaan diajukan, dana akan di-commit dari pagu sumber dana yang dipilih. Pastikan jumlah permintaan tidak melebihi sisa pagu yang tersedia.</p>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <x-card>
            <x-slot name="title">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-xl">
                        <x-heroicon-o-document-plus class="w-5 h-5 text-primary"/>
                    </div>
                    <div>
                        <span class="text-base font-semibold text-slate-800">Formulir Permintaan Dana</span>
                        <p class="text-xs text-slate-400 mt-0.5">Lengkapi data berikut untuk membuat permintaan baru</p>
                    </div>
                </div>
            </x-slot>

            <form action="{{ route('permintaan-dana.store') }}" method="POST">
                @csrf
                <div class="space-y-6" x-data="{
                    sumberDanas: {{ Js::from($sumberDanas->map(fn ($sd) => [
                        'id' => $sd->id,
                        'opd_id' => $sd->opd_id,
                        'label' => $sd->nama_sumber_dana,
                        'available' => $sd->availablePagu(),
                        'availableLabel' => 'Rp '.number_format($sd->availablePagu(), 0, ',', '.'),
                    ])) }},
                    opdId: {{ Js::from(old('opd_id')) }},
                    sumberDanaId: {{ Js::from(old('sumber_dana_id')) }},
                    jumlah: {{ Js::from(old('jumlah', '')) }},
                    filteredSumberDanas() {
                        return this.sumberDanas.filter(sd => sd.opd_id == this.opdId);
                    },
                    selectedAvailable: null,
                    onSumberChanged(e) {
                        const sd = this.sumberDanas.find(s => s.id == e.target.value);
                        this.selectedAvailable = sd ? sd : null;
                    },
                    get isAmountExceeds() {
                        if (!this.selectedAvailable || !this.jumlah) return false;
                        return Number(this.jumlah) > this.selectedAvailable.available;
                    },
                    get isAmountValid() {
                        if (!this.selectedAvailable || !this.jumlah) return false;
                        return Number(this.jumlah) > 0 && Number(this.jumlah) <= this.selectedAvailable.available;
                    }
                }">
                    {{-- Section: OPD & Sumber Dana --}}
                    <div>
                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">1</span>
                            Sumber Dana
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label>OPD <span class="text-red-500">*</span></x-input-label>
                                <select name="opd_id" x-model.number="opdId"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                                    <option value="">Pilih OPD</option>
                                    @foreach($opds as $opd)
                                        <option value="{{ $opd->id }}">{{ $opd->nama }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('opd_id')" class="mt-1"/>
                            </div>
                            <div>
                                <x-input-label>Sumber Dana <span class="text-red-500">*</span></x-input-label>
                                <select name="sumber_dana_id" x-model.number="sumberDanaId" @change="onSumberChanged($event)"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                                    <option value="">Pilih Sumber Dana</option>
                                    <template x-for="sd in filteredSumberDanas()" :key="sd.id">
                                        <option :value="sd.id" :disabled="sd.available <= 0" x-text="sd.label + ' (Sisa: ' + sd.availableLabel + ')'"></option>
                                    </template>
                                </select>
                                <x-input-error :messages="$errors->get('sumber_dana_id')" class="mt-1"/>
                            </div>
                        </div>

                        {{-- Available Pagu Indicator --}}
                        <template x-if="selectedAvailable">
                            <div class="mt-3 p-3 rounded-xl border"
                                 x-bind:class="isAmountExceeds ? 'bg-red-50 border-red-200' : 'bg-emerald-50 border-emerald-200'">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-banknotes class="w-4 h-4 shrink-0"
                                        x-bind:class="isAmountExceeds ? 'text-red-500' : 'text-emerald-500'"/>
                                    <div class="flex-1">
                                        <p class="text-xs font-medium"
                                           x-bind:class="isAmountExceeds ? 'text-red-700' : 'text-emerald-700'"
                                           x-text="'Sisa pagu tersedia: ' + selectedAvailable.availableLabel"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Section: Details --}}
                    <div>
                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">2</span>
                            Rincian Permintaan
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <x-input-label>Jumlah (Rp) <span class="text-red-500">*</span></x-input-label>
                                <x-text-input type="number" name="jumlah" x-model.number="jumlah" :value="old('jumlah')" min="1" step="1" placeholder="0" required/>
                                <x-input-error :messages="$errors->get('jumlah')" class="mt-1"/>
                                <template x-if="isAmountExceeds">
                                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                        <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5 shrink-0"/>
                                        Jumlah melebihi sisa pagu yang tersedia
                                    </p>
                                </template>
                                <template x-if="isAmountValid">
                                    <p class="mt-1.5 text-xs text-emerald-600 flex items-center gap-1">
                                        <x-heroicon-o-check-circle class="w-3.5 h-3.5 shrink-0"/>
                                        Dana mencukupi untuk permintaan ini
                                    </p>
                                </template>
                            </div>

                            <div>
                                <x-input-label>Keperluan <span class="text-red-500">*</span></x-input-label>
                                <x-text-input type="text" name="keperluan" :value="old('keperluan')" placeholder="Contoh: Pengadaan perlengkapan kantor" required/>
                                <x-input-error :messages="$errors->get('keperluan')" class="mt-1"/>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Additional Info --}}
                    <div>
                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">3</span>
                            Informasi Tambahan
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <x-input-label value="Tanggal"/>
                                <x-text-input type="date" name="tanggal" :value="old('tanggal', date('Y-m-d'))"/>
                                <x-input-error :messages="$errors->get('tanggal')" class="mt-1"/>
                            </div>
                            <div>
                                <x-input-label value="Catatan"/>
                                <textarea name="catatan" rows="3" placeholder="Catatan tambahan atau penjelasan mengenai permintaan ini (opsional)..."
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none">{{ old('catatan') }}</textarea>
                                <x-input-error :messages="$errors->get('catatan')" class="mt-1"/>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-5 border-t border-slate-100">
                        <a href="{{ route('permintaan-dana.index') }}"
                           class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-slate-300 rounded-xl font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                            Batal
                        </a>
                        <x-primary-button>
                            <x-heroicon-o-document-check class="w-4 h-4 mr-1.5"/>
                            Simpan sebagai Draft
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
