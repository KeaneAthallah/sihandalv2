<?php

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
use App\Http\Controllers\SumberDanaController;
use App\Http\Controllers\TahunAnggaranController;
use App\Http\Controllers\TransferDanaController;
use App\Http\Controllers\UserManagementController;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\Rekening;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/dashboard', function () {
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $opdScope = fn ($query, $column = 'opd_id') => $isAdmin ? $query : $query->where($column, $user->opd_id);

    $totalAnggaran = $opdScope(Opd::query(), 'id')->sum('total_pagu');
    $totalPenerimaan = $opdScope(Penerimaan::query())->sum('realisasi');
    $totalPengeluaran = $opdScope(Pengeluaran::query())->sum('realisasi');
    $permintaanPending = $opdScope(PermintaanDana::query())->where('status', 'menunggu')->count();
    $totalPagu = $totalAnggaran > 0 ? $totalAnggaran : 1;
    $sisaKas = Rekening::where('tipe', 'kas')->sum('saldo');
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

    $sumberDanaPenerimaan = $opdScope(Penerimaan::query())
        ->selectRaw('nama_sumber_dana, sum(realisasi) as total')
        ->whereNotNull('nama_sumber_dana')
        ->groupBy('nama_sumber_dana')
        ->pluck('total', 'nama_sumber_dana');

    return view('dashboard.index', compact(
        'totalAnggaran', 'totalPenerimaan', 'totalPengeluaran',
        'permintaanPending', 'sisaKas', 'sisaKasMax',
        'topOpd', 'recentPermintaan', 'statusCounts', 'sumberDanaPenerimaan'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/opd', [OpdController::class, 'index'])->name('opd.index');
    Route::get('/opd/{opd}', [OpdController::class, 'show'])->name('opd.show');

    Route::resource('sumber-dana', SumberDanaController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('rekening-kas', RekeningKasController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->parameters([
        'rekening-kas' => 'rekening',
    ]);
    Route::resource('program-kegiatan', ProgramKegiatanController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->parameters([
        'program-kegiatan' => 'program',
    ]);
    Route::resource('penerimaan', PenerimaanController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
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
