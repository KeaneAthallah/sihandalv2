<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PosisiKas;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\TransferDana;
use App\Models\User;

test('admin can create and update a sumber dana', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from('/sumber-dana/create')
        ->post('/sumber-dana', [
            'nama_sumber_dana' => 'Dana Alokasi Umum (DAU)',
        ]);

    $sumberDana = SumberDana::where('nama_sumber_dana', 'Dana Alokasi Umum (DAU)')->first();
    expect($sumberDana)->not->toBeNull();

    $this->actingAs($admin)
        ->from("/sumber-dana/{$sumberDana->id}/edit")
        ->put("/sumber-dana/{$sumberDana->id}", [
            'nama_sumber_dana' => 'Dana Alokasi Umum (DAU) - Baru',
        ]);

    expect($sumberDana->fresh()->nama_sumber_dana)->toBe('Dana Alokasi Umum (DAU) - Baru');
});

test('admin can create a program standalone', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/program-kegiatan', [
            'kode_program' => '1.2',
            'nama_program' => 'Program A',
        ]);

    $this->assertDatabaseHas('programs', ['kode_program' => '1.2', 'nama_program' => 'Program A']);
});

test('opd user store forces their own opd for kegiatan', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $program = Program::create(['kode_program' => '1.2', 'nama_program' => 'Program A']);

    $this->actingAs($userA)
        ->post("/program-kegiatan/{$program->id}/kegiatan", [
            'opd_id' => $opdB->id,
            'sumber_dana_id' => $sumberDana->id,
            'kode_kegiatan' => '1.2.3',
            'nama_kegiatan' => 'Pelatihan',
            'pagu' => 1000000,
            'realisasi' => 250000,
        ]);

    $this->assertDatabaseHas('kegiatan', [
        'nama_kegiatan' => 'Pelatihan',
        'program_id' => $program->id,
        'opd_id' => $opdA->id,
    ]);
});

test('multiple kegiatan can be added to the same program', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $program = Program::create(['kode_program' => '1.2', 'nama_program' => 'Program A']);

    $this->actingAs($admin)->post("/program-kegiatan/{$program->id}/kegiatan", [
        'opd_id' => $opd->id,
        'kode_kegiatan' => '1.2.1',
        'nama_kegiatan' => 'Pelatihan',
        'pagu' => 1000000,
        'realisasi' => 0,
    ]);
    $this->actingAs($admin)->post("/program-kegiatan/{$program->id}/kegiatan", [
        'opd_id' => $opd->id,
        'kode_kegiatan' => '1.2.2',
        'nama_kegiatan' => 'Sosialisasi',
        'pagu' => 500000,
        'realisasi' => 0,
    ]);

    $this->assertEquals(2, $program->kegiatans()->count());
});

test('kegiatan store recomputes persentase', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $program = Program::create(['kode_program' => '1.2', 'nama_program' => 'Program A']);

    $this->actingAs($admin)
        ->post("/program-kegiatan/{$program->id}/kegiatan", [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'kode_kegiatan' => '1.2.3',
            'nama_kegiatan' => 'Pelatihan',
            'pagu' => 1000000,
            'realisasi' => 250000,
        ]);

    $this->assertDatabaseHas('kegiatan', ['nama_kegiatan' => 'Pelatihan', 'persentase' => 25.00]);
});

test('admin can create and update a rekening kas', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/rekening-kas', [
            'kode' => '1.1.1',
            'nama' => 'Kas Umum',
            'tipe' => 'kas',
            'saldo' => 1000000,
        ]);

    $rekening = Rekening::where('kode', '1.1.1')->first();
    expect($rekening)->not->toBeNull();

    $this->actingAs($admin)
        ->put("/rekening-kas/{$rekening->id}", [
            'kode' => '1.1.1',
            'nama' => 'Kas Umum Daerah',
            'tipe' => 'kas',
            'saldo' => 2000000,
        ]);

    expect($rekening->fresh()->nama)->toBe('Kas Umum Daerah');
});

test('admin can create penerimaan master and persentase reflects transactions', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan', 'saldo' => 0]);

    $this->actingAs($admin)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'nama_sumber_dana' => 'PAD',
            'target' => 1000000,
        ]);

    $penerimaan = Penerimaan::where('nama_sumber_dana', 'PAD')->first();
    expect($penerimaan)->not->toBeNull()
        ->and((float) $penerimaan->persentase)->toBe(0.0);

    $this->actingAs($admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaan->id,
            'realisasi' => 300000,
            'tanggal' => now()->format('Y-m-d'),
        ]);

    expect((float) $penerimaan->fresh()->persentase)->toBe(30.0)
        ->and((float) $penerimaan->fresh()->realisasi)->toBe(300000.0);

    $this->actingAs($admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaan->id,
            'realisasi' => 200000,
            'tanggal' => now()->format('Y-m-d'),
        ]);

    $fresh = $penerimaan->fresh();
    expect((float) $fresh->persentase)->toBe(50.0)
        ->and((float) $fresh->realisasi)->toBe(500000.0);
});

test('opd user cannot edit penerimaan from another opd', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $penerimaanB = Penerimaan::create([
        'opd_id' => $opdB->id,
        'nama_sumber_dana' => 'PAD B',
        'target' => 1000000,
    ]);
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->get("/master-data/penerimaan/{$penerimaanB->id}/edit")
        ->assertForbidden();
});

test('opd user cannot create transaksi against another opd penerimaan', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $penerimaanB = Penerimaan::create([
        'opd_id' => $opdB->id,
        'nama_sumber_dana' => 'PAD B',
        'target' => 1000000,
    ]);
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaanB->id,
            'realisasi' => 100000,
            'tanggal' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors('penerimaan_id');

    expect(TransaksiPenerimaan::count())->toBe(0);
});

test('admin can create pengeluaran with persentase', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $this->actingAs($admin)
        ->post('/pengeluaran', [
            'opd_id' => $opd->id,
            'kegiatan_id' => null,
            'sumber_dana_id' => $sumberDana->id,
            'nama_kegiatan' => 'Rapat',
            'sumber_dana' => 'DAU',
            'anggaran' => 1000000,
            'realisasi' => 400000,
        ]);

    $pengeluaran = Pengeluaran::where('nama_kegiatan', 'Rapat')->first();
    expect($pengeluaran)->not->toBeNull()
        ->and((float) $pengeluaran->persentase)->toBe(40.0);
});

test('posisi kas computes saldo akhir on store and update', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Umum', 'tipe' => 'kas', 'saldo' => 0]);

    $this->actingAs($admin)
        ->post('/posisi-kas', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'saldo_awal' => 1000000,
            'penerimaan' => 500000,
            'pengeluaran' => 200000,
        ]);

    $posisiKas = PosisiKas::where('opd_id', $opd->id)->first();
    expect($posisiKas)->not->toBeNull()
        ->and((float) $posisiKas->saldo_akhir)->toBe(1300000.0);

    $this->actingAs($admin)
        ->put("/posisi-kas/{$posisiKas->id}", [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'saldo_awal' => 1000000,
            'penerimaan' => 0,
            'pengeluaran' => 100000,
        ]);

    expect((float) $posisiKas->fresh()->saldo_akhir)->toBe(900000.0);
});

test('transfer dana gets auto number and draft status', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $this->actingAs($admin)
        ->post('/transfer-dana', [
            'opd_id' => $opd->id,
            'jumlah' => 500000,
            'sumber_dana' => 'DAU',
            'keterangan' => 'Transfer operasional',
        ]);

    $transfer = TransferDana::where('opd_id', $opd->id)->first();
    expect($transfer)->not->toBeNull()
        ->and($transfer->nomor_transfer)->toStartWith('TF-')
        ->and($transfer->status)->toBe('draft');
});

test('transfer dana sets tanggal_selesai when marked selesai', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $transfer = TransferDana::create([
        'nomor_transfer' => 'TF-0001/2026',
        'opd_id' => $opd->id,
        'jumlah' => 500000,
        'sumber_dana' => 'DAU',
        'status' => 'diproses',
    ]);

    $this->actingAs($admin)
        ->put("/transfer-dana/{$transfer->id}", [
            'opd_id' => $opd->id,
            'jumlah' => 500000,
            'sumber_dana' => 'DAU',
            'status' => 'selesai',
        ]);

    expect($transfer->fresh()->tanggal_selesai)->not->toBeNull();
});

test('admin can destroy a sumber dana', function () {
    $admin = User::factory()->admin()->create();
    $sumber = SumberDana::create(['nama_sumber_dana' => 'DAK']);

    $this->actingAs($admin)->delete("/sumber-dana/{$sumber->id}");
    $this->assertDatabaseMissing('sumber_danas', ['id' => $sumber->id]);
});
