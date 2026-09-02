<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Belanja" :breadcrumbs="[$kegiatan->nama_kegiatan, $subKegiatan->nama_sub_kegiatan, 'Edit Belanja']">
            <x-slot name="actions">
                <x-secondary-button onclick="window.location='{{ route('belanja.index', $subKegiatan) }}'">
                    <x-heroicon-o-arrow-left class="w-4 h-4"/>
                    Kembali
                </x-secondary-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('belanja.update', ['subKegiatan' => $subKegiatan, 'belanja' => $belanja]) }}" method="POST">
            @csrf
            @method('PUT')

            <x-card title="Data Belanja">
                <div class="space-y-4">
                    <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm">
                        <span class="text-xs font-mono font-bold text-primary">{{ $subKegiatan->kode_sub_kegiatan }}</span>
                        <p class="text-slate-700 font-medium mt-0.5">{{ $subKegiatan->nama_sub_kegiatan }}</p>
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div>
                            <x-input-label for="opd_id" value="OPD" required/>
                            <select name="opd_id" id="opd_id" required class="mt-1 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">-- Pilih OPD --</option>
                                @foreach($opds as $opd)
                                    <option value="{{ $opd->id }}" @selected(old('opd_id', $belanja->opd_id) == $opd->id)>{{ $opd->nama }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('opd_id')"/>
                        </div>
                    @else
                        <input type="hidden" name="opd_id" value="{{ auth()->user()->opd_id }}"/>
                    @endif

                    <div>
                        <x-input-label for="rekening_id" value="Rekening" required/>
                        <select name="rekening_id" id="rekening_id" required class="mt-1 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">-- Pilih Rekening --</option>
                            @foreach($rekenings as $rekening)
                                <option value="{{ $rekening->id }}" @selected(old('rekening_id', $belanja->rekening_id) == $rekening->id)>{{ $rekening->kode }} — {{ $rekening->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('rekening_id')"/>
                    </div>

                    <div>
                        <x-input-label for="sumber_dana_id" value="Sumber Dana"/>
                        <select name="sumber_dana_id" id="sumber_dana_id" class="mt-1 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">-- Pilih Sumber Dana --</option>
                            @foreach($sumberDanas as $sumberDana)
                                <option value="{{ $sumberDana->id }}" @selected(old('sumber_dana_id', $belanja->sumber_dana_id) == $sumberDana->id)>{{ $sumberDana->nama_sumber_dana }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('sumber_dana_id')"/>
                    </div>

                    <div>
                        <x-input-label for="pagu" value="Pagu" required/>
                        <x-text-input type="number" name="pagu" id="pagu" class="mt-1" value="{{ old('pagu', $belanja->pagu) }}" placeholder="Masukkan pagu anggaran" step="0.01" min="0" required/>
                        <x-input-error :messages="$errors->get('pagu')"/>
                    </div>
                </div>
            </x-card>

            <div class="mt-5 flex items-center justify-end gap-3">
                <x-secondary-button type="button" onclick="window.location='{{ route('belanja.index', $subKegiatan) }}'">Batal</x-secondary-button>
                <x-primary-button>Simpan Perubahan</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>