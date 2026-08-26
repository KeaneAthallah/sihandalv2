<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <div class="mb-6">
        <h3 class="text-base font-semibold text-slate-800">Permintaan Dana Baru</h3>
        <p class="text-sm text-slate-400 mt-0.5">Buat permintaan dana dari OPD</p>
    </div>

    <form class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Permintaan</label>
            <input type="date" value="2026-07-14" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"/>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">OPD</label>
            <select class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <option value="">Pilih OPD</option>
                <option>Dinas Pendidikan Daerah</option>
                <option>Dinas Kesehatan Provinsi</option>
                <option>Dinas Sosial Provinsi</option>
                <option>Dinas Bina Marga</option>
                <option>Dinas Cipta Karya</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Sumber Dana</label>
            <select class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <option value="">Pilih Sumber Dana</option>
                <option>DAK</option>
                <option>DAU</option>
                <option>DBH</option>
                <option>PAD</option>
                <option>SILPA</option>
                <option>Hibah</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Program</label>
            <select class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <option value="">Pilih Program</option>
                <option>Pendidikan Menengah</option>
                <option>Kesehatan Masyarakat</option>
                <option>Infrastruktur Daerah</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Kegiatan</label>
            <select class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <option value="">Pilih Kegiatan</option>
                <option>Pengelolaan Pendidikan SMA</option>
                <option>Pelayanan Kesehatan Dasar</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Sub Kegiatan</label>
            <select class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <option value="">Pilih Sub Kegiatan</option>
                <option>Pembinaan Minat, Bakat dan Kreativitas Siswa</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Uraian Permintaan</label>
            <textarea rows="3" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none" placeholder="Deskripsikan detail permintaan..."></textarea>
        </div>
    </form>

    {{-- Financial Summary --}}
    <div class="mt-6 p-4 bg-slate-50 rounded-xl space-y-3">
        <div class="flex justify-between items-center">
            <span class="text-sm text-slate-500">Total Penerimaan</span>
            <span class="text-sm font-semibold text-emerald-600">Rp 45.800.000.000</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-slate-500">Total Pengeluaran</span>
            <span class="text-sm font-semibold text-red-600">Rp 28.300.000.000</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-slate-500">Posisi Kas Saat Ini</span>
            <span class="text-sm font-semibold text-slate-800">Rp 17.500.000.000</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-slate-500">Dana Di-commit</span>
            <span class="text-sm font-semibold text-amber-600">Rp 5.500.000.000</span>
        </div>
    </div>

    <div class="mt-4">
        <x-progress-card amount="Rp 12.000.000.000" :percentage="80" color="success"/>
    </div>

    {{-- Jumlah Permintaan --}}
    <div class="mt-4">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah Permintaan</label>
        <input type="text" placeholder="Rp 0" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"/>
        <div class="mt-2 flex items-center gap-2 p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
            <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-500 shrink-0"/>
            <span class="text-xs text-emerald-700">Dana tersedia. Permintaan dapat diajukan.</span>
        </div>
    </div>

    {{-- Upload --}}
    <div class="mt-4">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Lampiran</label>
        <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:border-primary/40 transition-all cursor-pointer">
            <x-heroicon-o-document-arrow-up class="w-8 h-8 text-slate-300 mx-auto mb-2"/>
            <p class="text-sm text-slate-500">Seret file ke sini atau <span class="text-primary font-medium">browse</span></p>
            <p class="text-xs text-slate-400 mt-1">PDF, maks. 10MB</p>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="mt-6 flex gap-3">
        <button class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">
            Simpan Draft
        </button>
        <button class="flex-1 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
            Ajukan Permintaan
        </button>
    </div>
</div>
