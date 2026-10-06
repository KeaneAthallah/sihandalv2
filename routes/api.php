<?php

use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BelanjaController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\KegiatanController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OpdController;
use App\Http\Controllers\Api\V1\PenerimaanController;
use App\Http\Controllers\Api\V1\PengeluaranController;
use App\Http\Controllers\Api\V1\PermintaanDanaController;
use App\Http\Controllers\Api\V1\PersetujuanController;
use App\Http\Controllers\Api\V1\PosisiKasController;
use App\Http\Controllers\Api\V1\ProgramController;
use App\Http\Controllers\Api\V1\RekeningBankController;
use App\Http\Controllers\Api\V1\RekeningController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SubKegiatanController;
use App\Http\Controllers\Api\V1\SumberDanaController;
use App\Http\Controllers\Api\V1\TahunAnggaranController;
use App\Http\Controllers\Api\V1\TransaksiPenerimaanBkuController;
use App\Http\Controllers\Api\V1\TransaksiPenerimaanController;
use App\Http\Controllers\Api\V1\TransferDanaController;
use Dedoc\Scramble\CacheableGenerator;
use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OpenAPI documentation aliases (required contract URLs)
|--------------------------------------------------------------------------
| Scramble serves the interactive docs at /docs/api and the schema at
| /docs/api.json. These aliases expose the same documents at the SIHANDAL
| contract URLs without registering a second documentation package.
*/
Route::get('/documentation', function () {
    return redirect('/docs/api');
})->name('api.documentation');

Route::get('/openapi.json', function (CacheableGenerator $generator) {
    $config = Scramble::getGeneratorConfig(Scramble::DEFAULT_API);

    return response()->json($generator($config), options: JSON_PRETTY_PRINT);
})->middleware(RestrictedDocsAccess::class)
    ->name('api.openapi');

Route::prefix('v1')->group(function (): void {

    // Public endpoints -------------------------------------------------------
    Route::get('health', HealthController::class)->name('api.v1.health');

    Route::prefix('auth')->name('api.v1.auth.')->group(function (): void {
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:api-auth')
            ->name('login');
    });

    // Authenticated endpoints ------------------------------------------------
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::post('auth/refresh', [AuthController::class, 'refresh'])->name('api.v1.auth.refresh');

        // Master data --------------------------------------------------------
        Route::apiResource('opds', OpdController::class)->only(['index', 'show']);
        Route::apiResource('programs', ProgramController::class);
        Route::apiResource('kegiatan', KegiatanController::class)->names('api.kegiatan');
        Route::apiResource('sub-kegiatan', SubKegiatanController::class)->names('api.sub-kegiatan');
        Route::apiResource('rekenings', RekeningController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::apiResource('sumber-dana', SumberDanaController::class)->names('api.sumber-dana');
        Route::get('tahun-anggaran-active', [TahunAnggaranController::class, 'active'])->name('tahun-anggaran.active');
        Route::apiResource('tahun-anggaran', TahunAnggaranController::class)->except(['destroy'])
            ->names('api.tahun-anggaran');
        Route::post('tahun-anggaran/{tahunAnggaran}/activate', [TahunAnggaranController::class, 'activate'])
            ->name('api.tahun-anggaran.activate');
        Route::apiResource('rekening-banks', RekeningBankController::class)->names('api.rekening-banks');
        Route::get('rekening-banks-active', [RekeningBankController::class, 'active'])
            ->name('api.rekening-banks.active');

        // Budget hierarchy ----------------------------------------------------
        Route::apiResource('belanja', BelanjaController::class)->except(['destroy'])
            ->names('api.belanja');
        Route::post('belanja/{belanja}/commit', [BelanjaController::class, 'commit'])->name('belanja.commit');
        Route::post('belanja/{belanja}/release', [BelanjaController::class, 'release'])->name('belanja.release');
        Route::post('belanja/{belanja}/realize', [BelanjaController::class, 'realize'])->name('belanja.realize');

        // Revenue -------------------------------------------------------------
        Route::apiResource('penerimaan', PenerimaanController::class)
            ->names('api.penerimaan');
        Route::apiResource('transaksi-penerimaan', TransaksiPenerimaanController::class)->except(['show'])
            ->names('api.transaksi-penerimaan');
        Route::get('transaksi-penerimaan/{transaksiPenerimaan}', [TransaksiPenerimaanController::class, 'show'])
            ->name('api.transaksi-penerimaan.show');
        Route::post('transaksi-penerimaan/{transaksiPenerimaan}/bkus', [TransaksiPenerimaanBkuController::class, 'store'])
            ->name('transaksi-penerimaan.bkus.store');
        Route::patch('transaksi-penerimaan/{transaksiPenerimaan}/bkus/{bku}', [TransaksiPenerimaanBkuController::class, 'update'])
            ->name('transaksi-penerimaan.bkus.update');
        Route::delete('transaksi-penerimaan/{transaksiPenerimaan}/bkus/{bku}', [TransaksiPenerimaanBkuController::class, 'destroy'])
            ->name('transaksi-penerimaan.bkus.destroy');

        // Expenditure & cash --------------------------------------------------
        Route::apiResource('pengeluaran', PengeluaranController::class)
            ->names('api.pengeluaran');
        Route::apiResource('posisi-kas', PosisiKasController::class)->except(['destroy'])
            ->names('api.posisi-kas');

        // Fund request workflow -----------------------------------------------
        Route::apiResource('permintaan-dana', PermintaanDanaController::class)->except(['destroy'])
            ->names('api.permintaan-dana');
        Route::post('permintaan-dana/{permintaanDana}/submit', [PermintaanDanaController::class, 'submit'])
            ->middleware('throttle:api-financial')
            ->name('api.permintaan-dana.submit');
        Route::get('permintaan-dana/{permintaanDana}/persetujuan', [PersetujuanController::class, 'index'])
            ->name('permintaan-dana.persetujuan.index');
        Route::post('permintaan-dana/{permintaanDana}/approve', [PersetujuanController::class, 'approve'])
            ->middleware(['admin', 'throttle:api-financial'])
            ->name('permintaan-dana.approve');
        Route::post('permintaan-dana/{permintaanDana}/reject', [PersetujuanController::class, 'reject'])
            ->middleware(['admin', 'throttle:api-financial'])
            ->name('permintaan-dana.reject');

        // Transfers ------------------------------------------------------------
        Route::apiResource('transfer-dana', TransferDanaController::class)
            ->names('api.transfer-dana');

        // Dashboard / reports / AI ---------------------------------------------
        Route::prefix('dashboard')->name('api.v1.dashboard.')->group(function (): void {
            Route::get('/', [DashboardController::class, 'index'])->name('index');
            Route::get('summary', [DashboardController::class, 'summary'])->name('summary');
            Route::get('budget', [DashboardController::class, 'budget'])->name('budget');
            Route::get('revenue', [DashboardController::class, 'revenue'])->name('revenue');
            Route::get('expenditure', [DashboardController::class, 'expenditure'])->name('expenditure');
            Route::get('cash', [DashboardController::class, 'cash'])->name('cash');
            Route::get('programs', [DashboardController::class, 'programs'])->name('programs');
            Route::get('activity', [DashboardController::class, 'activity'])->name('activity');
        });

        Route::prefix('reports')->name('api.v1.reports.')->group(function (): void {
            Route::get('penerimaan', [ReportController::class, 'penerimaan'])->name('penerimaan');
            Route::get('pengeluaran', [ReportController::class, 'pengeluaran'])->name('pengeluaran');
            Route::get('posisi-kas', [ReportController::class, 'posisiKas'])->name('posisi-kas');
            Route::get('permintaan-dana', [ReportController::class, 'permintaanDana'])->name('permintaan-dana');
            Route::get('penerimaan/export', [ReportController::class, 'exportPenerimaan'])->name('penerimaan.export');
            Route::get('pengeluaran/export', [ReportController::class, 'exportPengeluaran'])->name('pengeluaran.export');
            Route::get('posisi-kas/export', [ReportController::class, 'exportPosisiKas'])->name('posisi-kas.export');
            Route::get('permintaan-dana/export', [ReportController::class, 'exportPermintaanDana'])->name('permintaan-dana.export');
        });

        Route::prefix('ai')->name('api.v1.ai.')->middleware('throttle:api-read')->group(function (): void {
            Route::get('summary', [AiController::class, 'summary'])->name('summary');
            Route::get('budget-summary', [AiController::class, 'budgetSummary'])->name('budget-summary');
            Route::get('revenue-summary', [AiController::class, 'revenueSummary'])->name('revenue-summary');
            Route::get('expenditure-summary', [AiController::class, 'expenditureSummary'])->name('expenditure-summary');
            Route::get('cash-summary', [AiController::class, 'cashSummary'])->name('cash-summary');
            Route::get('program-summary', [AiController::class, 'programSummary'])->name('program-summary');
            Route::get('financial-alerts', [AiController::class, 'financialAlerts'])->name('financial-alerts');
        });

        // Notifications & audit -------------------------------------------------
        Route::get('notifications', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
        Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('api.v1.notifications.unread');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('api.v1.notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.v1.notifications.read-all');

        Route::get('audit-logs', [AuditLogController::class, 'index'])
            ->middleware('admin')
            ->name('api.v1.audit-logs.index');
    });
});
