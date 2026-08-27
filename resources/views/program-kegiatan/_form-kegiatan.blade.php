<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="{{ $idPrefix }}_opd_id" value="OPD" />
            <select name="opd_id" id="{{ $idPrefix }}_opd_id" required class="mt-1.5 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                <option value="">Pilih OPD</option>
                @foreach($opds as $opd)
                    <option value="{{ $opd->id }}" {{ old('opd_id', $kegiatan?->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('opd_id')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="{{ $idPrefix }}_sumber_dana_id" value="Sumber Dana" />
            <select name="sumber_dana_id" id="{{ $idPrefix }}_sumber_dana_id" class="mt-1.5 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                <option value="">Pilih Sumber Dana</option>
                @foreach($sumberDanas as $sd)
                    <option value="{{ $sd->id }}" {{ old('sumber_dana_id', $kegiatan?->sumber_dana_id) == $sd->id ? 'selected' : '' }}>{{ $sd->nama_sumber_dana }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('sumber_dana_id')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="{{ $idPrefix }}_kode_kegiatan" value="Kode Kegiatan" />
            <x-text-input type="text" name="kode_kegiatan" id="{{ $idPrefix }}_kode_kegiatan" value="{{ old('kode_kegiatan', $kegiatan?->kode_kegiatan) }}" placeholder="Misal: 1.1.2" required class="mt-1.5" />
            <x-input-error :messages="$errors->get('kode_kegiatan')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="{{ $idPrefix }}_kode_sub_kegiatan" value="Kode Sub Kegiatan" />
            <x-text-input type="text" name="kode_sub_kegiatan" id="{{ $idPrefix }}_kode_sub_kegiatan" value="{{ old('kode_sub_kegiatan', $kegiatan?->kode_sub_kegiatan) }}" placeholder="Opsional" class="mt-1.5" />
            <x-input-error :messages="$errors->get('kode_sub_kegiatan')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="{{ $idPrefix }}_nama_kegiatan" value="Nama Kegiatan" />
        <x-text-input type="text" name="nama_kegiatan" id="{{ $idPrefix }}_nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan?->nama_kegiatan) }}" placeholder="Masukkan nama kegiatan" required class="mt-1.5" />
        <x-input-error :messages="$errors->get('nama_kegiatan')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="{{ $idPrefix }}_nama_sub_kegiatan" value="Nama Sub Kegiatan" />
        <x-text-input type="text" name="nama_sub_kegiatan" id="{{ $idPrefix }}_nama_sub_kegiatan" value="{{ old('nama_sub_kegiatan', $kegiatan?->nama_sub_kegiatan) }}" placeholder="Opsional" class="mt-1.5" />
        <x-input-error :messages="$errors->get('nama_sub_kegiatan')" class="mt-1" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="{{ $idPrefix }}_kode_rekening" value="Kode Rekening" />
            <x-text-input type="text" name="kode_rekening" id="{{ $idPrefix }}_kode_rekening" value="{{ old('kode_rekening', $kegiatan?->kode_rekening) }}" placeholder="Opsional" class="mt-1.5" />
            <x-input-error :messages="$errors->get('kode_rekening')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="{{ $idPrefix }}_nama_rekening" value="Nama Rekening" />
            <x-text-input type="text" name="nama_rekening" id="{{ $idPrefix }}_nama_rekening" value="{{ old('nama_rekening', $kegiatan?->nama_rekening) }}" placeholder="Opsional" class="mt-1.5" />
            <x-input-error :messages="$errors->get('nama_rekening')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="{{ $idPrefix }}_pagu" value="Pagu Anggaran" />
            <x-text-input type="number" name="pagu" id="{{ $idPrefix }}_pagu" value="{{ old('pagu', $kegiatan?->pagu ?? 0) }}" placeholder="0" step="0.01" min="0" required class="mt-1.5" />
            <x-input-error :messages="$errors->get('pagu')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="{{ $idPrefix }}_realisasi" value="Realisasi" />
            <x-text-input type="number" name="realisasi" id="{{ $idPrefix }}_realisasi" value="{{ old('realisasi', $kegiatan?->realisasi ?? 0) }}" placeholder="0" step="0.01" min="0" class="mt-1.5" />
            <p class="text-xs text-slate-400 mt-1.5">Dapat dikosongkan atau diisi nanti.</p>
            <x-input-error :messages="$errors->get('realisasi')" class="mt-1" />
        </div>
    </div>
</div>
