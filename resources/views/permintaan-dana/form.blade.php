<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="px-5 lg:px-6 py-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-primary/10 rounded-xl">
                <x-heroicon-o-document-plus class="w-5 h-5 text-primary"/>
            </div>
            <div>
                <h3 class="text-base font-semibold text-slate-800">Permintaan Dana Baru</h3>
                <p class="text-xs text-slate-400 mt-0.5">Buat permintaan dana dari OPD</p>
            </div>
        </div>
    </div>

    <div class="px-5 lg:px-6 py-5 space-y-6">

        {{-- Step 1: Source --}}
        <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">1</span>
                Sumber Dana
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Permintaan</label>
                    <input type="date" value="2026-07-14" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">OPD</label>
                    <select class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <option value="">Pilih OPD</option>
                        <option>Dinas Pendidikan Daerah</option>
                        <option>Dinas Kesehatan Provinsi</option>
                        <option>Dinas Sosial Provinsi</option>
                        <option>Dinas Bina Marga</option>
                        <option>Dinas Cipta Karya</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Sumber Dana</label>
                <select class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    <option value="">Pilih Sumber Dana</option>
                    <option>DAK</option>
                    <option>DAU</option>
                    <option>DBH</option>
                    <option>PAD</option>
                    <option>SILPA</option>
                    <option>Hibah</option>
                </select>
            </div>
        </div>

        {{-- Step 2: Program Detail --}}
        <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">2</span>
                Rincian Program
            </h4>
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Program</label>
                        <select class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="">Pilih Program</option>
                            <option>Pendidikan Menengah</option>
                            <option>Kesehatan Masyarakat</option>
                            <option>Infrastruktur Daerah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Kegiatan</label>
                        <select class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="">Pilih Kegiatan</option>
                            <option>Pengelolaan Pendidikan SMA</option>
                            <option>Pelayanan Kesehatan Dasar</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Sub Kegiatan</label>
                    <select class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <option value="">Pilih Sub Kegiatan</option>
                        <option>Pembinaan Minat, Bakat dan Kreativitas Siswa</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Uraian Permintaan</label>
                    <textarea rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none" placeholder="Deskripsikan detail permintaan..."></textarea>
                </div>
            </div>
        </div>

        {{-- Financial Summary --}}
        <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">3</span>
                Ringkasan Keuangan
            </h4>
            <div class="p-4 bg-slate-50 rounded-xl space-y-3">
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
        </div>

        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200">
            <x-progress-card title="Sisa Kas Tersedia" amount="Rp 12.000.000.000" :percentage="80" color="success"/>
        </div>

        {{-- Amount --}}
        <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">4</span>
                Jumlah Permintaan
            </h4>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah (Rp)</label>
            <input type="text" placeholder="Rp 0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"/>
            <div class="mt-2 flex items-center gap-2 p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-500 shrink-0"/>
                <span class="text-xs text-emerald-700">Dana tersedia. Permintaan dapat diajukan.</span>
            </div>
        </div>

        {{-- Upload --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Lampiran</label>
            <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:border-primary/40 transition-all cursor-pointer">
                <x-heroicon-o-document-arrow-up class="w-8 h-8 text-slate-300 mx-auto mb-2"/>
                <p class="text-sm text-slate-500">Seret file ke sini atau <span class="text-primary font-medium">browse</span></p>
                <p class="text-xs text-slate-400 mt-1">PDF, maks. 10MB</p>
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="px-5 lg:px-6 py-4 border-t border-slate-100 flex gap-3">
        <button class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">
            Simpan Draft
        </button>
        <button class="flex-1 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
            Ajukan Permintaan
        </button>
    </div>
</div>
