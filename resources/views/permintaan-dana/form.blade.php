<x-card title="Permintaan Dana Baru">
    <div class="space-y-4">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Permintaan</label>
                <input type="date" value="2026-07-14" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"/>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">OPD</label>
                <select class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <option value="">Pilih OPD</option>
                    <option>Dinas Pendidikan Daerah</option>
                    <option>Dinas Kesehatan Provinsi</option>
                    <option>Dinas Sosial Provinsi</option>
                    <option>Dinas Bina Marga</option>
                    <option>Dinas Cipta Karya</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Sumber Dana</label>
            <select class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                <option value="">Pilih Sumber Dana</option>
                <option>DAK</option>
                <option>DAU</option>
                <option>DBH</option>
                <option>PAD</option>
                <option>SILPA</option>
                <option>Hibah</option>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Program</label>
                <select class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <option value="">Pilih Program</option>
                    <option>Pendidikan Menengah</option>
                    <option>Kesehatan Masyarakat</option>
                    <option>Infrastruktur Daerah</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Kegiatan</label>
                <select class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <option value="">Pilih Kegiatan</option>
                    <option>Pengelolaan Pendidikan SMA</option>
                    <option>Pelayanan Kesehatan Dasar</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Sub Kegiatan</label>
            <select class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                <option value="">Pilih Sub Kegiatan</option>
                <option>Pembinaan Minat, Bakat dan Kreativitas Siswa</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Uraian Permintaan</label>
            <textarea rows="3" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition resize-none" placeholder="Deskripsikan detail permintaan..."></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah (Rp)</label>
            <input type="text" placeholder="Rp 0" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"/>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Lampiran</label>
            <div class="border-2 border-dashed border-slate-200 rounded-lg p-6 text-center hover:border-primary/40 transition cursor-pointer">
                <p class="text-sm text-slate-500">Seret file ke sini atau <span class="text-primary font-medium">browse</span></p>
                <p class="text-xs text-slate-400 mt-1">PDF, maks. 10MB</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-6">
        <button type="button"
            class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
            Batal
        </button>
        <button type="submit"
            class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-dark transition">
            Simpan
        </button>
    </div>
</x-card>
