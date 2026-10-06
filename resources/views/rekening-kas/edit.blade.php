<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Rekening" :breadcrumbs="['Rekening Kas', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Form Edit Rekening">
            <form action="{{ route('rekening-kas.update', $rekening) }}" method="POST"
                  x-data="{
                      tipe: {{ json_encode((string) old('tipe', $rekening->tipe)) }},
                      parentId: {{ json_encode((string) old('parent_id', (string) ($rekening->parent_id ?? ''))) }},
                      rekenings: {{ Js::from($rekenings->map(fn ($r) => ['id' => (string) $r->id, 'kode' => $r->kode, 'nama' => $r->nama, 'tipe' => $r->tipe])->values()) }},
                      get indukOptions() {
                          return this.tipe ? this.rekenings.filter(r => r.tipe === this.tipe) : [];
                      }
                  }">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kode" value="Kode Rekening" />
                            <x-text-input type="text" name="kode" id="kode" value="{{ old('kode', $rekening->kode) }}" placeholder="Contoh: 1101" required class="mt-1.5" />
                            <x-input-error :messages="$errors->get('kode')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="tipe" value="Tipe Rekening" />
                            <select name="tipe" id="tipe" x-model="tipe" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">Pilih tipe...</option>
                                <option value="kas">Kas</option>
                                <option value="non-kas">Non-Kas</option>
                                <option value="pendapatan">Pendapatan</option>
                                <option value="belanja">Belanja</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipe')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="nama" value="Nama Rekening" />
                        <x-text-input type="text" name="nama" id="nama" value="{{ old('nama', $rekening->nama) }}" placeholder="Contoh: Kas Besar" required class="mt-1.5" />
                        <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                    </div>

                    <div x-show="tipe" x-cloak class="rounded-lg bg-slate-50 border border-slate-200 p-4">
                        <x-input-label for="parent_id" value="Rekening Induk (Opsional)" />
                        <p class="mt-1 text-xs text-slate-400">Pilih rekening induk untuk menjadikan rekening ini sebagai rekening detail (sub rekening). Kosongkan untuk mengembalikannya menjadi rekening utama. Induk harus bertipe sama.</p>
                        <select name="parent_id" id="parent_id" x-model="parentId" class="mt-2 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Tanpa Induk (Rekening Utama)</option>
                            <template x-for="r in indukOptions" :key="r.id">
                                <option :value="r.id" x-text="r.kode + ' - ' + r.nama"></option>
                            </template>
                        </select>
                        <x-input-error :messages="$errors->get('parent_id')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('rekening-kas.index') }}" class="btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                        Simpan
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
