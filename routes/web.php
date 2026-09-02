<?php

use App\Http\Controllers\BelanjaController;
use App\Http\Controllers\LaporanPenerimaanController;
use App\Http\Controllers\LaporanPengeluaranController;
use App\Http\Controllers\LaporanPosisiKasController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PermintaanDanaController;
use App\Http\Controllers\PersetujuanController;
use App\Http\Controllers\PosisiKasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramKegiatanController;
use App\Http\Controllers\RekapPermintaanDanaController;
use App\Http\Controllers\RekeningKasController;
use App\Http\Controllers\SubKegiatanController;
use App\Http\Controllers\SumberDanaController;
use App\Http\Controllers\TahunAnggaranController;
use App\Http\Controllers\TransaksiPenerimaanController;
use App\Http\Controllers\TransferDanaController;
use App\Http\Controllers\UptController;
use App\Http\Controllers\UserManagementController;
use App\Models\Belanja;
use App\Models\Opd;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/dashboard', function () {
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $opdScope = fn ($query, $column = 'opd_id') => $isAdmin ? $query : $query->where($column, $user->opd_id);

    $totalAnggaran = $opdScope(Opd::query(), 'id')->sum('total_pagu');
    $totalBelanja = $opdScope(Belanja::query())->sum('pagu');
    if ($totalBelanja > 0) {
        $totalAnggaran = $totalBelanja;
    }
    $totalPenerimaan = (float) DB::table('transaksi_penerimaans')
        ->join('penerimaans', 'penerimaans.id', '=', 'transaksi_penerimaans.penerimaan_id')
        ->when(! $isAdmin, fn ($q) => $q->where('penerimaans.opd_id', $user->opd_id))
        ->sum('transaksi_penerimaans.realisasi');
    $totalPengeluaran = $opdScope(Pengeluaran::query())->sum('realisasi');
    $permintaanPending = $opdScope(PermintaanDana::query())->where('status', 'menunggu')->count();
    $totalPagu = $totalAnggaran > 0 ? $totalAnggaran : 1;
    $kasPenerimaan = (float) DB::table('transaksi_penerimaans as t')
        ->join('penerimaans as p', 'p.id', '=', 't.penerimaan_id')
        ->join('rekenings as r', 'r.id', '=', 'p.rekening_id')
        ->when(! $isAdmin, fn ($q) => $q->where('p.opd_id', $user->opd_id))
        ->where('r.tipe', 'kas')
        ->sum('t.realisasi');
    $kasPengeluaran = (float) DB::table('pengeluarans')
        ->join('rekenings as r', 'r.id', '=', 'pengeluarans.rekening_id')
        ->when(! $isAdmin, fn ($q) => $q->where('pengeluarans.opd_id', $user->opd_id))
        ->where('r.tipe', 'kas')
        ->sum('pengeluarans.realisasi');
    $sisaKas = $kasPenerimaan - $kasPengeluaran;
    $sisaKasMax = $totalPagu > 0 ? $totalPagu : 1;

    $topOpd = $opdScope(Opd::query(), 'id')
        ->withSum('pengeluarans as total_realisasi_pengeluaran', 'realisasi')
        ->orderByDesc('total_realisasi_pengeluaran')
        ->take(10)
        ->get();

    $recentPermintaan = $opdScope(PermintaanDana::with('opd'))
        ->latest()
        ->take(6)
        ->get();

    $statusCounts = $opdScope(PermintaanDana::query())
        ->selectRaw('status, count(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status');

    $sumberDanaPenerimaan = DB::table('transaksi_penerimaans')
        ->join('penerimaans', 'penerimaans.id', '=', 'transaksi_penerimaans.penerimaan_id')
        ->leftJoin('sumber_danas as sd', 'sd.id', '=', 'penerimaans.sumber_dana_id')
        ->selectRaw('COALESCE(sd.nama_sumber_dana, penerimaans.nama_sumber_dana) as sumber_dana, sum(transaksi_penerimaans.realisasi) as total')
        ->when(! $isAdmin, fn ($q) => $q->where('penerimaans.opd_id', $user->opd_id))
        ->groupBy('sumber_dana')
        ->pluck('total', 'sumber_dana');

    return view('dashboard.index', compact(
        'totalAnggaran', 'totalPenerimaan', 'totalPengeluaran',
        'permintaanPending', 'sisaKas', 'sisaKasMax',
        'topOpd', 'recentPermintaan', 'statusCounts', 'sumberDanaPenerimaan'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/opd', [OpdController::class, 'index'])->name('opd.index');
    Route::get('/opd/{opd}', [OpdController::class, 'show'])->name('opd.show');

    Route::resource('upt', UptController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::resource('sumber-dana', SumberDanaController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('rekening-kas', RekeningKasController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->parameters([
        'rekening-kas' => 'rekening',
    ]);
    Route::resource('program-kegiatan', ProgramKegiatanController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->parameters([
        'program-kegiatan' => 'program',
    ]);
    Route::post('/program-kegiatan/{program}/kegiatan', [ProgramKegiatanController::class, 'storeKegiatan'])->name('program-kegiatan.kegiatan.store')->middleware('throttle:30');
    Route::put('/program-kegiatan/{program}/kegiatan/{kegiatan}', [ProgramKegiatanController::class, 'updateKegiatan'])->name('program-kegiatan.kegiatan.update')->middleware('throttle:30');
    Route::delete('/program-kegiatan/{program}/kegiatan/{kegiatan}', [ProgramKegiatanController::class, 'destroyKegiatan'])->name('program-kegiatan.kegiatan.destroy');

    Route::get('/kegiatan/{kegiatan}/sub-kegiatan', [SubKegiatanController::class, 'index'])->name('sub-kegiatan.index');
    Route::get('/kegiatan/{kegiatan}/sub-kegiatan/create', [SubKegiatanController::class, 'create'])->name('sub-kegiatan.create');
    Route::post('/kegiatan/{kegiatan}/sub-kegiatan', [SubKegiatanController::class, 'store'])->name('sub-kegiatan.store')->middleware('throttle:30');
    Route::get('/kegiatan/{kegiatan}/sub-kegiatan/{subKegiatan}/edit', [SubKegiatanController::class, 'edit'])->name('sub-kegiatan.edit');
    Route::put('/kegiatan/{kegiatan}/sub-kegiatan/{subKegiatan}', [SubKegiatanController::class, 'update'])->name('sub-kegiatan.update')->middleware('throttle:30');
    Route::delete('/kegiatan/{kegiatan}/sub-kegiatan/{subKegiatan}', [SubKegiatanController::class, 'destroy'])->name('sub-kegiatan.destroy');

    Route::get('/sub-kegiatan/{subKegiatan}/belanja', [BelanjaController::class, 'index'])->name('belanja.index');
    Route::get('/sub-kegiatan/{subKegiatan}/belanja/create', [BelanjaController::class, 'create'])->name('belanja.create');
    Route::post('/sub-kegiatan/{subKegiatan}/belanja', [BelanjaController::class, 'store'])->name('belanja.store')->middleware('throttle:30');
    Route::get('/sub-kegiatan/{subKegiatan}/belanja/{belanja}/edit', [BelanjaController::class, 'edit'])->name('belanja.edit');
    Route::put('/sub-kegiatan/{subKegiatan}/belanja/{belanja}', [BelanjaController::class, 'update'])->name('belanja.update')->middleware('throttle:30');
    Route::delete('/sub-kegiatan/{subKegiatan}/belanja/{belanja}', [BelanjaController::class, 'destroy'])->name('belanja.destroy');

    Route::resource('master-data/penerimaan', PenerimaanController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->names('master-data.penerimaan');
    Route::resource('transaksi-penerimaan', TransaksiPenerimaanController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->parameters([
        'transaksi-penerimaan' => 'transaksiPenerimaan',
    ]);
    Route::resource('pengeluaran', PengeluaranController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('posisi-kas', PosisiKasController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->parameters([
        'posisi-kas' => 'posisiKas',
    ]);

    Route::resource('permintaan-dana', PermintaanDanaController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('throttle:30');
    Route::post('/permintaan-dana/{permintaanDana}/submit', [PermintaanDanaController::class, 'submit'])
        ->name('permintaan-dana.submit')
        ->middleware('throttle:10');

    Route::get('/persetujuan', [PersetujuanController::class, 'index'])->name('persetujuan.index')->middleware('admin');
    Route::post('/persetujuan/{permintaanDana}/setujui', [PersetujuanController::class, 'setujui'])
        ->name('persetujuan.setujui')
        ->middleware(['admin', 'throttle:20']);
    Route::post('/persetujuan/{permintaanDana}/tolak', [PersetujuanController::class, 'tolak'])
        ->name('persetujuan.tolak')
        ->middleware(['admin', 'throttle:20']);

    Route::resource('transfer-dana', TransferDanaController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::get('/laporan-penerimaan', [LaporanPenerimaanController::class, 'index'])->name('laporan-penerimaan.index');
    Route::get('/laporan-penerimaan/export', [LaporanPenerimaanController::class, 'export'])->name('laporan-penerimaan.export');

    Route::get('/laporan-pengeluaran', [LaporanPengeluaranController::class, 'index'])->name('laporan-pengeluaran.index');
    Route::get('/laporan-pengeluaran/export', [LaporanPengeluaranController::class, 'export'])->name('laporan-pengeluaran.export');

    Route::get('/rekap-permintaan-dana', [RekapPermintaanDanaController::class, 'index'])->name('rekap-permintaan-dana.index');
    Route::get('/rekap-permintaan-dana/export', [RekapPermintaanDanaController::class, 'export'])->name('rekap-permintaan-dana.export');

    Route::get('/laporan-posisi-kas', [LaporanPosisiKasController::class, 'index'])->name('laporan-posisi-kas.index');
    Route::get('/laporan-posisi-kas/export', [LaporanPosisiKasController::class, 'export'])->name('laporan-posisi-kas.export');

    Route::resource('user-management', UserManagementController::class)
        ->except(['show'])
        ->parameters(['user-management' => 'user'])
        ->middleware(['admin', 'throttle:30']);

    Route::get('/tahun-anggaran', [TahunAnggaranController::class, 'index'])->name('tahun-anggaran.index')->middleware('admin');
    Route::post('/tahun-anggaran', [TahunAnggaranController::class, 'store'])->name('tahun-anggaran.store')->middleware('admin');
    Route::put('/tahun-anggaran/{tahunAnggaran}', [TahunAnggaranController::class, 'update'])->name('tahun-anggaran.update')->middleware('admin');
    Route::post('/tahun-anggaran/{tahunAnggaran}/activate', [TahunAnggaranController::class, 'activate'])->name('tahun-anggaran.activate')->middleware('admin');
    Route::delete('/tahun-anggaran/{tahunAnggaran}', [TahunAnggaranController::class, 'destroy'])->name('tahun-anggaran.destroy')->middleware('admin');

    Route::get('/pengaturan', function () {
        return view('pengaturan.index');
    })->name('pengaturan.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
