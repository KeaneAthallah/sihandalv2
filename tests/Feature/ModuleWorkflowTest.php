<?php

use App\Models\Opd;
use App\Models\Pengeluaran;
use App\Models\Penerimaan;
use App\Models\PosisiKas;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TransferDana;
use App\Models\User;

test('admin can create and update a sumber dana with recomputed persentase', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $this->actingAs($admin)
        ->from('/sumber-dana/create')
        ->post('/sumber-dana', [
            'opd_id' => $opd->id,
            'nama_sumber_dana' => 'DAU',
            'pagu' => 2000000,
            'realisasi' => 500000,
        ]);

    $sumberDana = SumberDana::where('nama_sumber_dana', 'DAU')->first();
    expect($sumberDana)->not->toBeNull()
        ->and((float) $sumberDana->persentase)->toBe(25.0);

    $this->actingAs($admin)
        ->from("/sumber-dana/{$sumberDana->id}/edit")
        ->put("/sumber-dana/{$sumberDana->id}", [
            'opd_id' => $opd->id,
            'nama_sumber_dana' => 'DAU',
            'pagu' => 4000000,
            'realisasi' => 1000000,
        ]);

    expect((float) $sumberDana->fresh()->persentase)->toBe(25.0);
});

test('opd user store forces their own opd for program', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->from('/program-kegiatan/create')
        ->post('/program-kegiatan', [
            'opd_id' => $opdB->id,
            'kode_kegiatan' => '1.2.3',
            'nama_kegiatan' => 'Pelatihan',
            'sumber_dana' => 'DAU',
            'pagu' => 1000000,
            'realisasi' => 250000,
        ]);

    $this->assertDatabaseHas('programs', [
        'nama_kegiatan' => 'Pelatihan',
        'opd_id' => $opdA->id,
    ]);
});

test('program store recomputes persentase', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $this->actingAs($admin)
        ->post('/program-kegiatan', [
            'opd_id' => $opd->id,
            'kode_kegiatan' => '1.2.3',
            'nama_kegiatan' => 'Pelatihan',
            'sumber_dana' => 'DAU',
            'pagu' => 1000000,
            'realisasi' => 250000,
        ]);

    $this->assertDatabaseHas('programs', ['nama_kegiatan' => 'Pelatihan', 'persentase' => 25.00]);
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

test('admin can create penerimaan with persentase and opd scoping on edit', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan', 'saldo' => 0]);

    $this->actingAs($admin)
        ->post('/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'nama_sumber_dana' => 'PAD',
            'target' => 1000000,
            'realisasi' => 300000,
        ]);

    $penerimaan = Penerimaan::where('nama_sumber_dana', 'PAD')->first();
    expect($penerimaan)->not->toBeNull()
        ->and((float) $penerimaan->persentase)->toBe(30.0);

    $this->actingAs($admin)
        ->put("/penerimaan/{$penerimaan->id}", [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'nama_sumber_dana' => 'PAD',
            'target' => 2000000,
            'realisasi' => 500000,
        ]);

    expect((float) $penerimaan->fresh()->persentase)->toBe(25.0);
});

test('opd user cannot edit penerimaan from another opd', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $penerimaanB = Penerimaan::create([
        'opd_id' => $opdB->id,
        'nama_sumber_dana' => 'PAD B',
        'target' => 1000000,
        'realisasi' => 0,
    ]);
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->get("/penerimaan/{$penerimaanB->id}/edit")
        ->assertForbidden();
});

test('admin can create pengeluaran with persentase', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $this->actingAs($admin)
        ->post('/pengeluaran', [
            'opd_id' => $opd->id,
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

test('opd user can destroy only their own sumber dana', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $sumberB = SumberDana::create(['opd_id' => $opdB->id, 'nama_sumber_dana' => 'DAK', 'pagu' => 1000000, 'realisasi' => 0]);
    $sumberA = SumberDana::create(['opd_id' => $opdA->id, 'nama_sumber_dana' => 'DAU', 'pagu' => 1000000, 'realisasi' => 0]);
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)->delete("/sumber-dana/{$sumberB->id}");
    $this->assertDatabaseHas('sumber_danas', ['id' => $sumberB->id]);

    $this->actingAs($userA)->delete("/sumber-dana/{$sumberA->id}");
    $this->assertDatabaseMissing('sumber_danas', ['id' => $sumberA->id]);
});
