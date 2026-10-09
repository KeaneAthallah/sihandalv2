<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Services\SihandalImportService;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\XlsxFixture;

function backfillCsvPath(): string
{
    return dirname(__DIR__).'/Fixtures/sumberdana-fixture.csv';
}

function backfillXlsxPath(): string
{
    $path = sys_get_temp_dir().'/sihandal-backfill-fixture.xlsx';
    XlsxFixture::build($path);

    return $path;
}

function runBackfillImport(): void
{
    app(SihandalImportService::class)->import(backfillCsvPath(), backfillXlsxPath());
}

function nullLegacyColumns(): void
{
    DB::table('kegiatan')->update([
        'kode_sub_kegiatan' => null,
        'nama_sub_kegiatan' => null,
        'rekening_id' => null,
        'kode_rekening' => null,
        'nama_rekening' => null,
    ]);
}

test('backfill fills legacy columns from the first sub kegiatan and belanja', function () {
    runBackfillImport();
    nullLegacyColumns();

    $this->artisan('app:backfill-kegiatan-legacy')->assertExitCode(0);

    $cipta = Opd::where('kode', '1.03.0.00.0.00.02.0000')->first();
    $kegiatan = Kegiatan::where('opd_id', $cipta->id)
        ->where('kode_kegiatan', '1.03.07.1.01')
        ->first();

    expect($kegiatan->kode_sub_kegiatan)->toBe('1.03.07.1.01.0026')
        ->and($kegiatan->nama_sub_kegiatan)->toBe('Pembangunan SPAM')
        ->and($kegiatan->kode_rekening)->toBe('5.1.02.02.008.00021')
        ->and($kegiatan->nama_rekening)->toBe('Belanja Jasa Konsultansi')
        ->and($kegiatan->rekening_id)->not->toBeNull();

    // The hierarchy itself is untouched.
    expect(SubKegiatan::count())->toBe(3)
        ->and(Belanja::count())->toBe(4);
});

test('backfill is idempotent', function () {
    runBackfillImport();
    nullLegacyColumns();

    $this->artisan('app:backfill-kegiatan-legacy')->assertExitCode(0);
    $first = Kegiatan::where('kode_kegiatan', '1.03.07.1.01')
        ->get(['kode_sub_kegiatan', 'kode_rekening'])
        ->toArray();

    $this->artisan('app:backfill-kegiatan-legacy')->assertExitCode(0);
    $second = Kegiatan::where('kode_kegiatan', '1.03.07.1.01')
        ->get(['kode_sub_kegiatan', 'kode_rekening'])
        ->toArray();

    expect($second)->toBe($first);
});

test('backfill skips kegiatan without children without failing', function () {
    runBackfillImport();
    nullLegacyColumns();

    $kegiatan = Kegiatan::create([
        'program_id' => Program::first()->id,
        'opd_id' => Opd::first()->id,
        'kode_kegiatan' => '9.9.9.9',
        'nama_kegiatan' => 'Childless Kegiatan',
    ]);

    $this->artisan('app:backfill-kegiatan-legacy')->assertExitCode(0);

    expect($kegiatan->fresh()->kode_sub_kegiatan)->toBeNull()
        ->and(Kegiatan::where('kode_kegiatan', '1.03.07.1.01')->whereNotNull('kode_sub_kegiatan')->exists())->toBeTrue();
});
