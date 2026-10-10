<?php

use App\Models\Opd;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

function buildImportWorkbook(array $headers, array $rows): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray($headers, null, 'A1');
    $row = 2;

    foreach ($rows as $cells) {
        $sheet->fromArray($cells, null, 'A'.$row);
        $row++;
    }

    $path = sys_get_temp_dir().'/'.uniqid('sihandal-import-', true).'.xlsx';
    IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);

    return $path;
}

function importUpload(string $path, string $name = 'import.xlsx'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, (string) file_get_contents($path));
}

test('admin can download the template for every importable module', function () {
    $admin = User::factory()->admin()->create();

    $cases = [
        ['upt.import.template', 'template-upt.xlsx'],
        ['rekening-kas.import.template', 'template-rekening-kas.xlsx'],
        ['program-kegiatan.import.template', 'template-program-kegiatan.xlsx'],
        ['master-data.penerimaan.import.template', 'template-penerimaan.xlsx'],
    ];

    foreach ($cases as [$route, $filename]) {
        $response = $this->actingAs($admin)->get(route($route));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        expect($response->headers->get('Content-Disposition'))->toContain($filename);
    }
});

test('upt import creates a new upt then upserts it by kode', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $path = buildImportWorkbook(
        ['Kode', 'Nama UPT', 'OPD'],
        [['UPT-1', 'UPT Lama', 'OPD-A']]
    );

    $this->actingAs($admin)->post('/upt/import', ['file' => importUpload($path)])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('upts', [
        'kode' => 'UPT-1',
        'nama' => 'UPT Lama',
        'opd_id' => $opd->id,
    ]);

    // Re-import with a different nama; the OPD is resolved by nama this time.
    $path = buildImportWorkbook(
        ['Kode', 'Nama UPT', 'OPD'],
        [['UPT-1', 'UPT Baru', 'Dinas A']]
    );

    $this->actingAs($admin)->post('/upt/import', ['file' => importUpload($path)])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('upts', 1);
    $this->assertDatabaseHas('upts', ['kode' => 'UPT-1', 'nama' => 'UPT Baru']);
});

test('upt import accepts csv uploads', function () {
    $admin = User::factory()->admin()->create();
    Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $csv = "Kode,Nama UPT,OPD\nUPT-1,UPT CSV,OPD-A\n";

    $this->actingAs($admin)->post('/upt/import', [
        'file' => UploadedFile::fake()->createWithContent('upt.csv', $csv),
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('upts', ['kode' => 'UPT-1', 'nama' => 'UPT CSV']);
});

test('rekening import resolves the parent by kode', function () {
    $admin = User::factory()->admin()->create();
    $induk = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Induk', 'tipe' => 'kas']);

    $path = buildImportWorkbook(
        ['Kode', 'Nama Rekening', 'Tipe', 'Kode Induk (opsional)'],
        [['1.1.1.01', 'Kas Anak', 'kas', '1.1.1']]
    );

    $this->actingAs($admin)->post('/rekening-kas/import', ['file' => importUpload($path)])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('rekenings', [
        'kode' => '1.1.1.01',
        'tipe' => 'kas',
        'parent_id' => $induk->id,
    ]);
});

test('rekening import rejects a child whose tipe differs from its parent', function () {
    $admin = User::factory()->admin()->create();
    Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Induk', 'tipe' => 'kas']);

    $path = buildImportWorkbook(
        ['Kode', 'Nama Rekening', 'Tipe', 'Kode Induk (opsional)'],
        [['1.1.1.01', 'Kas Anak Salah', 'pendapatan', '1.1.1']]
    );

    $this->actingAs($admin)->post('/rekening-kas/import', ['file' => importUpload($path)])
        ->assertSessionHasErrors(['file']);

    expect(session('import_errors'))->not->toBeEmpty();
    $this->assertDatabaseMissing('rekenings', ['kode' => '1.1.1.01']);
});

test('rekening import rejects an unknown tipe', function () {
    $admin = User::factory()->admin()->create();

    $path = buildImportWorkbook(
        ['Kode', 'Nama Rekening', 'Tipe', 'Kode Induk (opsional)'],
        [['1.1.1.01', 'Kas Anak', 'modal', null]]
    );

    $this->actingAs($admin)->post('/rekening-kas/import', ['file' => importUpload($path)])
        ->assertSessionHasErrors(['file']);

    $this->assertDatabaseMissing('rekenings', ['kode' => '1.1.1.01']);
});

test('rekening import rejects a rekening being its own parent', function () {
    $admin = User::factory()->admin()->create();
    Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Induk', 'tipe' => 'kas']);

    $path = buildImportWorkbook(
        ['Kode', 'Nama Rekening', 'Tipe', 'Kode Induk (opsional)'],
        [['1.1.1', 'Kas Induk Diedit', 'kas', '1.1.1']]
    );

    $this->actingAs($admin)->post('/rekening-kas/import', ['file' => importUpload($path)])
        ->assertSessionHasErrors(['file']);

    // The existing induk is left untouched.
    $this->assertDatabaseHas('rekenings', ['kode' => '1.1.1', 'nama' => 'Kas Induk']);
});

test('program and kegiatan import creates a program with its kegiatan then updates pagu', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'Dana Alokasi Umum (DAU)']);
    $tahunAnggaran = TahunAnggaran::create([
        'tahun' => 2026,
        'tanggal_mulai' => '2026-01-01',
        'tanggal_selesai' => '2026-12-31',
    ]);

    $headers = [
        'Kode Program', 'Nama Program', 'Kode Kegiatan', 'Nama Kegiatan', 'OPD',
        'Sumber Dana (opsional)', 'Kode Rekening (opsional)', 'Tahun Anggaran (opsional)',
        'Pagu', 'Realisasi (opsional)',
    ];

    $path = buildImportWorkbook($headers, [
        ['1.2', 'Program Penunjang', '1.2.3', 'Penyelenggaraan', 'OPD-A', 'Dana Alokasi Umum (DAU)', null, '2026', 500000000, 100000000],
    ]);

    $this->actingAs($admin)->post('/program-kegiatan/import', ['file' => importUpload($path)])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('programs', 1);
    $this->assertDatabaseHas('kegiatan', [
        'kode_kegiatan' => '1.2.3',
        'sumber_dana_id' => $sumberDana->id,
        'tahun_anggaran_id' => $tahunAnggaran->id,
        'pagu' => 500000000,
        'realisasi' => 100000000,
        'persentase' => 20,
    ]);

    // Re-import with a larger pagu; the program is not duplicated.
    $path = buildImportWorkbook($headers, [
        ['1.2', 'Program Penunjang', '1.2.3', 'Penyelenggaraan', 'Dinas A', 'Dana Alokasi Umum (DAU)', null, '2026', 1000000000, 250000000],
    ]);

    $this->actingAs($admin)->post('/program-kegiatan/import', ['file' => importUpload($path)])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('programs', 1);
    $this->assertDatabaseHas('kegiatan', [
        'kode_kegiatan' => '1.2.3',
        'pagu' => 1000000000,
        'realisasi' => 250000000,
        'persentase' => 25,
    ]);
});

test('penerimaan import upserts by its composite key', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $utama = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan Utama', 'tipe' => 'pendapatan']);
    Rekening::create(['kode' => '4.1.1.01', 'nama' => 'Sub Pendapatan', 'tipe' => 'pendapatan', 'parent_id' => $utama->id]);

    $headers = ['OPD', 'Rekening Utama (opsional)', 'Sub Rekening (opsional)', 'Tahun Anggaran (opsional)', 'Target'];

    $path = buildImportWorkbook($headers, [['OPD-A', '4.1.1', '4.1.1.01', null, 1000000000]]);

    $this->actingAs($admin)->post('/master-data/penerimaan/import', ['file' => importUpload($path)])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('penerimaans', 1);
    $this->assertDatabaseHas('penerimaans', [
        'opd_id' => $opd->id,
        'rekening_id' => $utama->id,
        'target' => 1000000000,
    ]);

    // Same composite key with a new target updates in place; the
    // rekening utama is resolved by nama this time.
    $path = buildImportWorkbook($headers, [['Dinas A', 'Pendapatan Utama', '4.1.1.01', null, 2000000000]]);

    $this->actingAs($admin)->post('/master-data/penerimaan/import', ['file' => importUpload($path)])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('penerimaans', 1);
    $this->assertDatabaseHas('penerimaans', ['target' => 2000000000]);
});

test('penerimaan import rejects a sub rekening that is not a child of the rekening utama', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $utama = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan Utama', 'tipe' => 'pendapatan']);
    Rekening::create(['kode' => '4.1.2', 'nama' => 'Pendapatan Lain', 'tipe' => 'pendapatan']);

    $headers = ['OPD', 'Rekening Utama (opsional)', 'Sub Rekening (opsional)', 'Tahun Anggaran (opsional)', 'Target'];

    $path = buildImportWorkbook($headers, [['OPD-A', '4.1.1', '4.1.2', null, 100000000]]);

    $this->actingAs($admin)->post('/master-data/penerimaan/import', ['file' => importUpload($path)])
        ->assertSessionHasErrors(['file']);

    expect(session('import_errors'))->not->toBeEmpty();
    $this->assertDatabaseCount('penerimaans', 0);
});

test('penerimaan import rejects a non-pendapatan rekening utama', function () {
    $admin = User::factory()->admin()->create();
    Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Umum', 'tipe' => 'kas']);

    $headers = ['OPD', 'Rekening Utama (opsional)', 'Sub Rekening (opsional)', 'Tahun Anggaran (opsional)', 'Target'];

    $path = buildImportWorkbook($headers, [['OPD-A', '1.1.1', null, null, 100000000]]);

    $this->actingAs($admin)->post('/master-data/penerimaan/import', ['file' => importUpload($path)])
        ->assertSessionHasErrors(['file']);

    $this->assertDatabaseCount('penerimaans', 0);
});

test('an invalid row is reported and nothing is persisted', function () {
    $admin = User::factory()->admin()->create();
    Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $path = buildImportWorkbook(
        ['Kode', 'Nama UPT', 'OPD'],
        [
            ['UPT-1', 'UPT Valid', 'OPD-A'],
            ['UPT-2', 'UPT OPD Salah', 'OPD-TIDAK-ADA'],
        ]
    );

    $this->actingAs($admin)->post('/upt/import', ['file' => importUpload($path)])
        ->assertSessionHasErrors(['file']);

    $errors = session('import_errors');
    expect($errors)->toHaveCount(1);
    expect($errors[0])->toContain('Baris 3');
    expect($errors[0])->toContain('OPD-TIDAK-ADA');

    $this->assertDatabaseCount('upts', 0);
});

test('a workbook with a missing header is rejected', function () {
    $admin = User::factory()->admin()->create();

    $path = buildImportWorkbook(['Kode', 'OPD'], [['UPT-1', 'OPD-A']]);

    $this->actingAs($admin)->post('/upt/import', ['file' => importUpload($path)])
        ->assertSessionHasErrors(['file']);

    $this->assertDatabaseCount('upts', 0);
});

test('opd users cannot import or download templates', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $requests = [
        ['get', '/upt/import/template'],
        ['post', '/upt/import'],
        ['get', '/rekening-kas/import/template'],
        ['post', '/rekening-kas/import'],
        ['get', '/program-kegiatan/import/template'],
        ['post', '/program-kegiatan/import'],
        ['get', '/master-data/penerimaan/import/template'],
        ['post', '/master-data/penerimaan/import'],
    ];

    foreach ($requests as [$method, $uri]) {
        $this->actingAs($user)->{$method}($uri)->assertForbidden();
    }
});

test('a non-spreadsheet upload is rejected', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/upt/import', [
        'file' => UploadedFile::fake()->create('upt.pdf', 100),
    ])->assertSessionHasErrors(['file']);
});
