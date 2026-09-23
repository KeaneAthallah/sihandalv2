<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Master Data Penerimaan" :breadcrumbs="['Keuangan', 'Master Data Penerimaan', 'Tambah Baru']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Form Master Penerimaan Baru">
            <form action="{{ route('master-data.penerimaan.store') }}" method="POST">
                @csrf

                @php
                    $initialDetails = old('details', [['sumber_dana_id' => '']]);
                @endphp

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
                            @foreach($rekenings->where('tipe', 'pendapatan') as $rekening)
                                <option value="{{ $rekening->id }}" {{ old('rekening_id') == $rekening->id ? 'selected' : '' }}>{{ $rekening->kode . ' - ' . $rekening->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('rekening_id')" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Sumber Dana" />
                            <select name="sumber_dana_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">Pilih Sumber Dana</option>
                                @foreach($sumberDanas as $sd)
                                    <option value="{{ $sd->id }}" {{ old('sumber_dana_id') == $sd->id ? 'selected' : '' }}>{{ $sd->nama_sumber_dana }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('sumber_dana_id')" />
                        </div>
                        <div>
                            <x-input-label value="Tahun Anggaran" />
                            <select name="tahun_anggaran_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">Pilih Tahun Anggaran</option>
                                @foreach($tahunAnggarans as $ta)
                                    <option value="{{ $ta->id }}" {{ old('tahun_anggaran_id') == $ta->id ? 'selected' : '' }}>{{ $ta->tahun }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tahun_anggaran_id')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Target" />
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                            <x-text-input name="target" type="number" :value="old('target', '0')" step="0.01" min="0" placeholder="0" class="pl-10" required />
                        </div>
                        <x-input-error :messages="$errors->get('target')" />
                        <p class="mt-1 text-xs text-slate-400">Realisasi dicatat melalui Transaksi Penerimaan secara terpisah.</p>
                    </div>

                    <div
                        x-data="{
                            details: {{ Js::from($initialDetails) }},
                            addDetail() {
                                this.details.push({ sumber_dana_id: '' });
                            },
                            removeDetail(index) {
                                this.details.splice(index, 1);
                            }
                        }"
                        class="rounded-lg border border-slate-200 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <x-input-label value="Detail Sumber Dana (Opsional)" />
                                <p class="mt-1 text-xs text-slate-400">Catat Sumber Dana pada level detail. Satu Penerimaan dapat memiliki beberapa detail.</p>
                            </div>
                            <button type="button" @click="addDetail()" class="text-sm font-medium text-primary hover:underline shrink-0">
                                + Tambah Detail
                            </button>
                        </div>

                        <div class="mt-3 space-y-2">
                            <template x-for="(detail, index) in details" :key="index">
                                <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-2 items-center">
                                    <div>
                                        <select :name="`details[${index}][sumber_dana_id]`" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                            <option value="">Pilih Sumber Dana</option>
                                            @foreach($sumberDanas as $sd)
                                                <option value="{{ $sd->id }}" :selected="detail.sumber_dana_id == {{ $sd->id }}">{{ $sd->nama_sumber_dana }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" @click="removeDetail(index)" x-show="details.length > 1" class="px-2 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition shrink-0">
                                        Hapus
                                    </button>
                                </div>
                            </template>
                        </div>

                        @php
                            $detailErrors = collect($errors->toArray())->filter(fn ($value, $key) => str_starts_with($key, 'details.'));
                        @endphp
                        @if ($detailErrors->isNotEmpty())
                            <div class="mt-2 space-y-1">
                                @foreach ($detailErrors as $messages)
                                    @foreach ($messages as $message)
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('master-data.penerimaan.index') }}" class="btn-secondary">
                        Batal
                    </a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
