<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\TransaksiPenerimaan;
use App\Services\SihandalImportService;
use Tests\Fixtures\XlsxFixture;

function fixtureCsvPath(): string
{
    return dirname(__DIR__).'/Fixtures/sumberdana-fixture.csv';
}

function fixtureXlsxPath(): string
{
    $path = sys_get_temp_dir().'/sihandal-import-fixture.xlsx';
    XlsxFixture::build($path);

    return $path;
}

function runImporter(bool $fresh = false): array
{
    $csv = fixtureCsvPath();
    $xlsx = fixtureXlsxPath();

    return app(SihandalImportService::class)->import($csv, $xlsx, $fresh);
}

test('budget CSV builds the full hierarchy and reconciles exactly', function () {
    $report = runImporter();

    expect($report['budget']['reconciled'])->toBeTrue()
        ->and($report['budget']['processed'])->toBe(4)
        ->and($report['budget']['created'])->toBe(4);

    // Expected SUM(ROUND(PAGU,2)) for the fixture: 1750000000 + 100000000 + 50000000 + 250000000.06
    $expectedRounded = 1750000000.0 + 100000000.0 + 50000000.0 + 250000000.06;
    expect($report['budget']['expectedSumRounded'])->toBe($expectedRounded)
        ->and(abs($report['budget']['actualSum'] - $expectedRounded) < 0.01)->toBeTrue();

    // 40/50 distinct values reduced to the fixture's few masters.
    expect(Opd::count())->toBe(2)
        ->and(Program::count())->toBe(2)
        ->and(SubKegiatan::count())->toBe(3)
        ->and(Belanja::count())->toBe(4);

    // Derived parent aggregates are persisted (views rely on deze columns).
    $subkekSum = SubKegiatan::where('kode_sub_kegiatan', '1.03.07.1.01.0026')->sum('pagu');
    expect($subkekSum)->toBe(1750000000.0 + 100000000.0 + 250000000.06);

    $keg = Kegiatan::where('kode_kegiatan', '1.03.07.1.01')
        ->whereHas('opd', fn ($q) => $q->where('kode', '1.03.0.00.0.00.02.0000'))
        ->first();
    expect($keg)->not->toBeNull();
});

test('budget rollups are persisted on kegiatan and opd total_pagu', function () {
    runImporter();

    $cipta = Opd::where('kode', '1.03.0.00.0.00.02.0000')->first();
    $expectedCipta = (float) Belanja::where('opd_id', $cipta->id)->sum('pagu');
    expect((float) $cipta->total_pagu)->toBe($expectedCipta);

    $keg = Kegiatan::where('kode_kegiatan', '1.03.07.1.01')
        ->whereHas('opd', fn ($q) => $q->where('kode', '1.03.0.00.0.00.02.0000'))
        ->first();
    $expectedKeg = (float) SubKegiatan::whereIn('id', Belanja::where('opd_id', $cipta->id)->pluck('sub_kegiatan_id'))
        ->sum('pagu');
    expect((float) $keg->pagu)->toBe($expectedKeg);
});

test('two CSV rows under the same sub kegiatan become two distinct belanjas', function () {
    runImporter();

    $cipta = Opd::where('kode', '1.03.0.00.0.00.02.0000')->first();
    $program = Program::where('kode_program', '1.03.07')->first();
    $kegiatan = Kegiatan::where('opd_id', $cipta->id)
        ->where('kode_kegiatan', '1.03.07.1.01')
        ->first();
    $sub = SubKegiatan::where('kegiatan_id', $kegiatan->id)
        ->where('kode_sub_kegiatan', '1.03.07.1.01.0026')
        ->first();

    expect($program)->not->toBeNull()
        ->and($kegiatan->program_id)->toBe($program->id)
        ->and($sub)->not->toBeNull();

    // Rows 1 and 2 both live under this sub kegiatan (different rekening).
    $belanjas = Belanja::where('sub_kegiatan_id', $sub->id)->get();

    expect($belanjas)->toHaveCount(2)
        ->and($belanjas->pluck('pagu')->map(fn ($p) => (float) $p))
        ->toContain(1750000000.0, 100000000.0);
});

test('re-import is idempotent and never doubles budget or revenue', function () {
    runImporter();

    $belanjaBefore = Belanja::count();
    $transaksiBefore = TransaksiPenerimaan::count();

    runImporter();

    expect(Belanja::count())->toBe($belanjaBefore)
        ->and(TransaksiPenerimaan::count())->toBe($transaksiBefore)
        ->and(Belanja::count())->toBe(4)
        ->and(Penerimaan::count())->toBe(3);
});

test('revenue XLSX import stores province-wide transactions grouped by master', function () {
    runImporter();

    // Each workbook row becomes a transaction, grouped under a master per
    // Sumber Dana (SILPA, PAD, BLUD).
    expect(TransaksiPenerimaan::count())->toBe(5)
        ->and(Penerimaan::count())->toBe(3);

    $allNullOpd = Penerimaan::whereNotNull('opd_id')->count() === 0;
    expect($allNullOpd)->toBeTrue();

    $saldoAwal = TransaksiPenerimaan::where('keterangan', 'SALDO AWAL 2026')->first();
    expect($saldoAwal)->not->toBeNull()
        ->and($saldoAwal->penerimaan->nama_sumber_dana)->toBe('SILPA');
});

test('revenue total reconciles to the XLSX column H sum', function () {
    $report = runImporter();

    expect($report['revenue']['reconciled'])->toBeTrue()
        ->and(abs($report['revenue']['actualSum'] - XlsxFixture::rekeningsPaguReconciled()) < 0.01)->toBeTrue();
});

test('row traceability columns are populated from the source', function () {
    runImporter();

    $belanja1 = Belanja::where('source_row', 1)->first();
    expect($belanja1)->not->toBeNull()
        ->and($belanja1->source_file)->toBe('sumberdana-fixture.csv')
        ->and($belanja1->source_identifier)->toBe('1');

    $penerimaan = TransaksiPenerimaan::where('source_row', 8)->first();
    expect($penerimaan)->not->toBeNull()
        ->and($penerimaan->source_file)->toBe(basename(fixtureXlsxPath()));
});

test('fiscal year 2026 is created and open', function () {
    runImporter();

    $ta = TahunAnggaran::where('tahun', '2026')->first();
    expect($ta)->not->toBeNull()
        ->and($ta->status)->toBe('open')
        ->and($ta->is_active)->toBeTrue();
});

test('sumber dana names are preserved without case folding', function () {
    runImporter();

    expect(SumberDana::where('nama_sumber_dana', 'Dana Bagi Hasil (DBH)')->exists())->toBeTrue()
        ->and(SumberDana::where('nama_sumber_dana', 'Dana Alokasi Umum (DAU)')->exists())->toBeTrue();
});

test('a malformed CSV row is recorded as a failure, not silently dropped', function () {
    $path = sys_get_temp_dir().'/sihandal-broken-'.uniqid().'.csv';
    file_put_contents($path, implode("\n", [
        'NO,KDSKPD,NMSKPD,KDSUBUNIT,NMSUBUNIT,KDKEGIATAN,NMKEGIATAN,KDSUBKEGIATAN,NMSUBKEGIATAN,KDREK,NMREK,PAGU,SUMBERDANA',
        '1,OPD-X,,, ,,Keg,1.2.3.1,Sub,5.1.1,Belanja,100,Dana',
        '2,OPD-X,,, ,1.2.3,Keg,1.2.3.1,Sub,5.1.1,Belanja,100,Dana',
    ]));

    $report = app(SihandalImportService::class)->import($path, fixtureXlsxPath(), false);

    expect($report['failureCount'])->toBe(1)
        ->and($report['failures'][0]['row'])->toBe(2)
        ->and(Belanja::where('source_row', 2)->exists())->toBeTrue();
});

test('import is transactional and applies fully across both source files', function () {
    $report = runImporter(true);

    expect($report['budget']['created'])->toBe(4)
        ->and($report['revenue']['created'])->toBe(5);
});

test('dry run validates and reconciles without persisting anything', function () {
    $csv = fixtureCsvPath();
    $xlsx = fixtureXlsxPath();

    $report = app(SihandalImportService::class)->import($csv, $xlsx, false, true);

    // The dry run reports the would-be counts and reconciliation…
    expect($report['budget']['created'])->toBe(4)
        ->and($report['revenue']['created'])->toBe(5)
        ->and($report['budget']['reconciled'])->toBeTrue()
        ->and($report['revenue']['reconciled'])->toBeTrue();

    // …but persists nothing.
    expect(Opd::count())->toBe(0)
        ->and(Belanja::count())->toBe(0)
        ->and(Penerimaan::count())->toBe(0)
        ->and(Program::count())->toBe(0);
});
