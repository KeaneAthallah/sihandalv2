<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ showForm: false }">
    {{-- Column 1: Request Information --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <x-heroicon-o-document-text class="w-5 h-5 text-primary"/>
            Informasi Permintaan
        </h3>

        <div class="space-y-3">
            <div class="flex justify-between items-start">
                <span class="text-sm text-slate-500">Nomor Permintaan</span>
                <span class="text-sm font-medium text-slate-800 text-right">PD-2026-001</span>
            </div>
            <div class="flex justify-between items-start">
                <span class="text-sm text-slate-500">Tanggal</span>
                <span class="text-sm font-medium text-slate-800">10 Juli 2026</span>
            </div>
            <div class="flex justify-between items-start">
                <span class="text-sm text-slate-500">OPD</span>
                <span class="text-sm font-medium text-slate-800 text-right max-w-[200px]">Dinas Pendidikan Daerah Prov. Sulawesi Tengah</span>
            </div>
            <div class="flex justify-between items-start">
                <span class="text-sm text-slate-500">Sumber Dana</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">DAK</span>
            </div>
            <div class="border-t border-slate-100 pt-3">
                <div class="flex justify-between items-start">
                    <span class="text-sm text-slate-500">Program</span>
                    <span class="text-sm font-medium text-slate-800 text-right max-w-[200px]">Pendidikan Menengah</span>
                </div>
            </div>
            <div class="flex justify-between items-start">
                <span class="text-sm text-slate-500">Kegiatan</span>
                <span class="text-sm font-medium text-slate-800 text-right max-w-[200px]">Pengelolaan Pendidikan SMA</span>
            </div>
            <div class="flex justify-between items-start">
                <span class="text-sm text-slate-500">Sub Kegiatan</span>
                <span class="text-sm font-medium text-slate-800 text-right max-w-[200px]">Pembinaan Minat, Bakat dan Kreativitas Siswa</span>
            </div>
            <div class="border-t border-slate-100 pt-3">
                <div class="flex justify-between items-start">
                    <span class="text-sm text-slate-500">Uraian</span>
                    <span class="text-sm text-slate-700 text-right max-w-[200px]">Pengadaan perlengkapan dan peralatan untuk kegiatan ekstrakurikuler siswa SMA/SMK</span>
                </div>
            </div>
            <div class="flex justify-between items-start">
                <span class="text-sm text-slate-500">Lampiran</span>
                <span class="text-sm text-primary font-medium cursor-pointer hover:underline">SPD_001.pdf</span>
            </div>
            <div class="border-t border-slate-100 pt-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Nilai Permintaan</span>
                    <span class="text-lg font-bold text-slate-800">Rp 2.500.000.000</span>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button class="w-full px-4 py-2.5 border-2 border-red-300 text-red-600 text-sm font-semibold rounded-xl hover:bg-red-50 transition-all">
                Batalkan Permintaan
            </button>
        </div>
    </div>

    {{-- Column 2: Financial Summary --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <x-heroicon-o-currency-dollar class="w-5 h-5 text-primary"/>
            Ringkasan Keuangan
        </h3>

        <div class="space-y-3">
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

        <div class="mt-5">
            <x-progress-card amount="Rp 12.000.000.000" :percentage="80" color="success"/>
        </div>

        <div class="mt-5 p-4 bg-slate-50 rounded-xl space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500">Prioritas</span>
                <x-priority-badge priority="high"/>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500">Dibuat Oleh</span>
                <span class="text-sm font-medium text-slate-700">Kasubid Anggaran</span>
            </div>
        </div>
    </div>

    {{-- Column 3: Timeline --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <x-heroicon-o-clock class="w-5 h-5 text-primary"/>
            Timeline Proses
        </h3>

        <x-timeline :currentStep="3" :steps="[
            'Permintaan Diajukan',
            'Verifikasi OPD',
            'Verifikasi BPKAD',
            'Persetujuan PPKD',
            'SPD Diterbitkan',
            'SPM',
            'SP2D',
            'Selesai',
        ]"/>

        <div class="mt-6 flex gap-3">
            <button class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">
                Edit Permintaan
            </button>
            <button class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">
                Lihat Riwayat
            </button>
        </div>
    </div>
</div>
