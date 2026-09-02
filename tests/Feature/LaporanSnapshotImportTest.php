<?php

use App\Models\LaporanSnapshot;
use App\Models\Pengeluaran;
use App\Models\PosisiKas;
use App\Models\TransaksiPenerimaan;
use App\Services\LaporanSnapshotImportService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('laporan');
});

function laporanDisk(): Filesystem
{
    return Storage::disk('laporan');
}

/**
 * Write a minimal, reconcile-able revenue document to the laporan disk.
 */
function putRevenueFixture(string $filename): string
{
    $doc = [
        'jenis' => 'realisasi_pendapatan',
        'judul' => 'LAPORAN REALISASI PENDAPATAN DAERAH',
        'periode' => 'BULAN FIXTURE 2026',
        'tahun_anggaran' => 2026,
        'tanggal_laporan' => '2026-02-01',
        'signed_by' => 'Dra. FATNINI, M.Si',
        'total_pendapatan' => [
            'target' => 300_000_000.0,
            'realisasi' => 55_000_000.0,
        ],
        'records' => [
            ['section' => 'pendapatan', 'tipe_baris' => 'rincian', 'kode' => '4.1', 'level' => 2, 'uraian' => 'PAD', 'target' => 200_000_000.0, 'realisasi_sd_bulan_ini' => 40_000_000.0],
            ['section' => 'pendapatan', 'tipe_baris' => 'rincian', 'kode' => '4.2', 'level' => 2, 'uraian' => 'TRANSFER', 'target' => 100_000_000.0, 'realisasi_sd_bulan_ini' => 15_000_000.0, 'persentase' => 15.0],
            ['section' => 'pendapatan', 'tipe_baris' => 'total', 'kode' => 'TOTAL', 'level' => 1, 'uraian' => 'TOTAL PENDAPATAN', 'target' => 300_000_000.0, 'realisasi_sd_bulan_ini' => 55_000_000.0],
        ],
    ];

    laporanDisk()->put($filename, json_encode($doc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    return $filename;
}

/**
 * Write a posisi-kas fixture that reconciles (Jumlah I + Jumlah II sisa)
 * against jumlah_saldo_buku.
 */
function putPosisiKasFixture(string $filename): string
{
    $doc = [
        'jenis' => 'posisi_kas',
        'judul' => 'LAPORAN POSISI KAS DAERAH',
        'periode' => 'PER 08 JUNI 2026',
        'tahun_anggaran' => 2026,
        'tanggal_laporan' => '2026-06-08',
        'signed_by' => 'Dra. FATNINI, M.Si',
        'jumlah_saldo_buku' => 50_000_000.0,
        'records' => [
            ['section' => 'saldo_buku', 'tipe_baris' => 'rincian', 'uraian' => 'PENERIMAAN', 'nilai' => 80_000_000.0],
            ['section' => 'saldo_buku', 'tipe_baris' => 'rincian', 'uraian' => 'PENGELUARAN', 'nilai' => 30_000_000.0],
            ['section' => 'posisi_silpa_2025', 'tipe_baris' => 'rincian', 'uraian' => 'SiLPA x', 'penerimaan' => 20_000_000.0, 'pengeluaran' => 0.0, 'sisa' => 20_000_000.0],
            ['section' => 'posisi_silpa_2025', 'tipe_baris' => 'total', 'uraian' => 'Jumlah I', 'penerimaan' => 20_000_000.0, 'pengeluaran' => 0.0, 'sisa' => 20_000_000.0],
            ['section' => 'posisi_realisasi_2026', 'tipe_baris' => 'rincian', 'uraian' => 'DAU', 'penerimaan' => 60_000_000.0, 'pengeluaran' => 30_000_000.0, 'sisa' => 30_000_000.0],
            ['section' => 'posisi_realisasi_2026', 'tipe_baris' => 'total', 'uraian' => 'Jumlah II', 'penerimaan' => 60_000_000.0, 'pengeluaran' => 30_000_000.0, 'sisa' => 30_000_000.0],
        ],
    ];

    laporanDisk()->put($filename, json_encode($doc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    return $filename;
}

test('revenue snapshot import persists records and reconciles exactly', function () {
    $file = putRevenueFixture('rev-fixture.json');

    $report = app(LaporanSnapshotImportService::class)->import($file);

    expect(LaporanSnapshot::count())->toBe(3)
        ->and($report['records']['realisasi_pendapatan']['created'])->toBe(3)
        ->and($report['records']['realisasi_pendapatan']['reconciled'])->toBeTrue()
        ->and($report['records']['realisasi_pendapatan']['imported'])->toBe(55_000_000.0);

    $row = LaporanSnapshot::where('kode', '4.1')->first();
    expect($row)->not->toBeNull()
        ->and($row->source_file)->toBe($file)
        ->and($row->source_row)->toBe(1)
        ->and($row->source_identifier)->toBe('4.1')
        ->and($row->periode)->toBe('BULAN FIXTURE 2026')
        ->and($row->tanggal_laporan->toDateString())->toBe('2026-02-01');
});

test('posisi kas snapshot reconciles jumlah buku from Jumlah I + Jumlah II', function () {
    $file = putPosisiKasFixture('posisi-fixture.json');

    $report = app(LaporanSnapshotImportService::class)->import($file);

    expect($report['records']['posisi_kas']['reconciled'])->toBeTrue()
        ->and($report['records']['posisi_kas']['imported'])->toBe(50_000_000.0);
});

test('dry run validates and reconciles without persisting anything', function () {
    $file = putRevenueFixture('dry-run-rev.json');

    $report = app(LaporanSnapshotImportService::class)->import($file, true);

    expect($report['records']['realisasi_pendapatan']['created'])->toBe(3)
        ->and($report['records']['realisasi_pendapatan']['reconciled'])->toBeTrue()
        ->and(LaporanSnapshot::count())->toBe(0);
});

test('re-import is idempotent and never doubles snapshot rows', function () {
    $file = putRevenueFixture('idem-rev.json');

    app(LaporanSnapshotImportService::class)->import($file);
    $before = LaporanSnapshot::count();

    $report = app(LaporanSnapshotImportService::class)->import($file);

    expect(LaporanSnapshot::count())->toBe($before)
        ->and(LaporanSnapshot::count())->toBe(3)
        ->and($report['records']['realisasi_pendapatan']['created'])->toBe(0)
        ->and($report['records']['realisasi_pendapatan']['skipped'])->toBe(3);
});

test('import is transactional and rolls back on failure', function () {
    // A document missing a required key is rejected before any row is written.
    $bad = [
        'jenis' => 'realisasi_pendapatan',
        'records' => [],
    ];
    laporanDisk()->put('bad.json', json_encode($bad));

    $this->expectException(RuntimeException::class);
    app(LaporanSnapshotImportService::class)->import('bad.json');
});

test('snapshot import never creates or alters transactional data', function () {
    $transaksiBefore = TransaksiPenerimaan::count();
    $pengeluaranBefore = Pengeluaran::count();
    $posisiBefore = PosisiKas::count();

    app(LaporanSnapshotImportService::class)->import(putRevenueFixture('no-transact.json'));
    app(LaporanSnapshotImportService::class)->import(putPosisiKasFixture('no-transact-pos.json'));

    expect(TransaksiPenerimaan::count())->toBe($transaksiBefore)
        ->and(Pengeluaran::count())->toBe($pengeluaranBefore)
        ->and(PosisiKas::count())->toBe($posisiBefore);
});

test('reconciliation reports a mismatch when import totals differ from the report', function () {
    $doc = [
        'jenis' => 'realisasi_pendapatan',
        'periode' => 'BULAN MISMATCH',
        'tanggal_laporan' => '2026-02-01',
        'total_pendapatan' => ['realisasi' => 99_999.0],
        'records' => [
            ['section' => 'pendapatan', 'tipe_baris' => 'rincian', 'kode' => '4.1', 'level' => 2, 'uraian' => 'PAD', 'realisasi_sd_bulan_ini' => 40_000.0],
        ],
    ];
    laporanDisk()->put('mismatch.json', json_encode($doc));

    $report = app(LaporanSnapshotImportService::class)->import('mismatch.json');

    expect($report['records']['realisasi_pendapatan']['reconciled'])->toBeFalse()
        ->and($report['records']['realisasi_pendapatan']['expected'])->toBe(99_999.0)
        ->and($report['records']['realisasi_pendapatan']['imported'])->toBe(40_000.0);
});

test('all laporan JSON files are imported by the directory import', function () {
    putRevenueFixture('dir-a.json');
    putPosisiKasFixture('dir-b.json');

    $report = app(LaporanSnapshotImportService::class)->importDir();

    expect(LaporanSnapshot::count())->toBe(3 + 6);
});

test('directory import is atomic and rolls back every file when one is invalid', function () {
    putRevenueFixture('atomic-good.json');
    laporanDisk()->put('atomic-bad.json', json_encode(['jenis' => 'realisasi_pendapatan', 'records' => []]));

    $this->expectException(RuntimeException::class);
    app(LaporanSnapshotImportService::class)->importDir();

    expect(LaporanSnapshot::count())->toBe(0);
});

test('artisan import command without a file imports all snapshots', function () {
    putRevenueFixture('artisan-rev.json');

    $this->artisan('app:import-laporan-snapshots')
        ->expectsConfirmation('Import all laporan JSON snapshots on the laporan disk? Continue?', 'yes')
        ->assertExitCode(0);

    expect(LaporanSnapshot::count())->toBe(3);
});
