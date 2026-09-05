<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\User;

test('admin can create penerimaan with pendapatan rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);

    $this->actingAs($admin)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'target' => 1000000,
        ])
        ->assertSessionHasNoErrors('rekening_id');

    $this->assertDatabaseHas('penerimaans', [
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
    ]);
});

test('cannot create penerimaan with belanja rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '5.1.1', 'nama' => 'Belanja Pegawai', 'tipe' => 'belanja']);

    $this->actingAs($admin)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('rekening_id');

    $this->assertDatabaseMissing('penerimaans', [
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
    ]);
});

test('cannot create penerimaan with kas rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Umum', 'tipe' => 'kas']);

    $this->actingAs($admin)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('rekening_id');

    $this->assertDatabaseMissing('penerimaans', [
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
    ]);
});

test('cannot update penerimaan to belanja rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekeningPendapatan = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);
    $rekeningBelanja = Rekening::create(['kode' => '5.1.1', 'nama' => 'Belanja Pegawai', 'tipe' => 'belanja']);

    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id,
        'rekening_id' => $rekeningPendapatan->id,
        'target' => 1000000,
    ]);

    $this->actingAs($admin)
        ->from("/master-data/penerimaan/{$penerimaan->id}/edit")
        ->put("/master-data/penerimaan/{$penerimaan->id}", [
            'opd_id' => $opd->id,
            'rekening_id' => $rekeningBelanja->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('rekening_id');

    expect($penerimaan->fresh()->rekening_id)->toBe($rekeningPendapatan->id);
});

test('cannot update penerimaan to kas rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekeningPendapatan = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);
    $rekeningKas = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Umum', 'tipe' => 'kas']);

    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id,
        'rekening_id' => $rekeningPendapatan->id,
        'target' => 1000000,
    ]);

    $this->actingAs($admin)
        ->from("/master-data/penerimaan/{$penerimaan->id}/edit")
        ->put("/master-data/penerimaan/{$penerimaan->id}", [
            'opd_id' => $opd->id,
            'rekening_id' => $rekeningKas->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('rekening_id');

    expect($penerimaan->fresh()->rekening_id)->toBe($rekeningPendapatan->id);
});

test('admin can create pengeluaran with belanja rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '5.1.1', 'nama' => 'Belanja Pegawai', 'tipe' => 'belanja']);

    $this->actingAs($admin)
        ->post('/pengeluaran', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'sumber_dana_id' => $sumberDana->id,
            'anggaran' => 500000,
            'realisasi' => 200000,
        ])
        ->assertSessionHasNoErrors('rekening_id');

    $this->assertDatabaseHas('pengeluarans', [
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
    ]);
});

test('cannot create pengeluaran with pendapatan rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);

    $this->actingAs($admin)
        ->from('/pengeluaran/create')
        ->post('/pengeluaran', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'sumber_dana_id' => $sumberDana->id,
            'anggaran' => 500000,
            'realisasi' => 200000,
        ])
        ->assertSessionHasErrors('rekening_id');

    $this->assertDatabaseMissing('pengeluarans', [
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
    ]);
});

test('cannot create pengeluaran with kas rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Umum', 'tipe' => 'kas']);

    $this->actingAs($admin)
        ->from('/pengeluaran/create')
        ->post('/pengeluaran', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'sumber_dana_id' => $sumberDana->id,
            'anggaran' => 500000,
            'realisasi' => 200000,
        ])
        ->assertSessionHasErrors('rekening_id');

    $this->assertDatabaseMissing('pengeluarans', [
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
    ]);
});

test('cannot update pengeluaran to pendapatan rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekeningBelanja = Rekening::create(['kode' => '5.1.1', 'nama' => 'Belanja Pegawai', 'tipe' => 'belanja']);
    $rekeningPendapatan = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);

    $pengeluaran = Pengeluaran::create([
        'opd_id' => $opd->id,
        'rekening_id' => $rekeningBelanja->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'anggaran' => 500000,
        'realisasi' => 200000,
    ]);

    $this->actingAs($admin)
        ->from("/pengeluaran/{$pengeluaran->id}/edit")
        ->put("/pengeluaran/{$pengeluaran->id}", [
            'opd_id' => $opd->id,
            'rekening_id' => $rekeningPendapatan->id,
            'sumber_dana_id' => $sumberDana->id,
            'anggaran' => 500000,
            'realisasi' => 200000,
        ])
        ->assertSessionHasErrors('rekening_id');

    expect($pengeluaran->fresh()->rekening_id)->toBe($rekeningBelanja->id);
});

test('cannot update pengeluaran to kas rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekeningBelanja = Rekening::create(['kode' => '5.1.1', 'nama' => 'Belanja Pegawai', 'tipe' => 'belanja']);
    $rekeningKas = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Umum', 'tipe' => 'kas']);

    $pengeluaran = Pengeluaran::create([
        'opd_id' => $opd->id,
        'rekening_id' => $rekeningBelanja->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'anggaran' => 500000,
        'realisasi' => 200000,
    ]);

    $this->actingAs($admin)
        ->from("/pengeluaran/{$pengeluaran->id}/edit")
        ->put("/pengeluaran/{$pengeluaran->id}", [
            'opd_id' => $opd->id,
            'rekening_id' => $rekeningKas->id,
            'sumber_dana_id' => $sumberDana->id,
            'anggaran' => 500000,
            'realisasi' => 200000,
        ])
        ->assertSessionHasErrors('rekening_id');

    expect($pengeluaran->fresh()->rekening_id)->toBe($rekeningBelanja->id);
});

test('opd user cannot create penerimaan with wrong tipe rekening', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $rekening = Rekening::create(['kode' => '5.1.1', 'nama' => 'Belanja Pegawai', 'tipe' => 'belanja']);

    $this->actingAs($user)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('rekening_id');

    $this->assertDatabaseMissing('penerimaans', [
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
    ]);
});

test('opd user cannot create pengeluaran with wrong tipe rekening', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);

    $this->actingAs($user)
        ->from('/pengeluaran/create')
        ->post('/pengeluaran', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'sumber_dana_id' => $sumberDana->id,
            'anggaran' => 500000,
            'realisasi' => 200000,
        ])
        ->assertSessionHasErrors('rekening_id');

    $this->assertDatabaseMissing('pengeluarans', [
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
    ]);
});

test('penerimaan error message is in indonesian', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '5.1.1', 'nama' => 'Belanja Pegawai', 'tipe' => 'belanja']);

    $this->actingAs($admin)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors([
            'rekening_id' => 'Rekening penerimaan harus bertipe pendapatan.',
        ]);
});

test('pengeluaran error message is in indonesian', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);

    $this->actingAs($admin)
        ->from('/pengeluaran/create')
        ->post('/pengeluaran', [
            'opd_id' => $opd->id,
            'rekening_id' => $rekening->id,
            'sumber_dana_id' => $sumberDana->id,
            'anggaran' => 500000,
            'realisasi' => 200000,
        ])
        ->assertSessionHasErrors([
            'rekening_id' => 'Rekening pengeluaran harus bertipe belanja.',
        ]);
});

test('nullable rekening_id is still allowed for penerimaan', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $this->actingAs($admin)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => null,
            'target' => 1000000,
        ])
        ->assertSessionHasNoErrors('rekening_id');

    $this->assertDatabaseHas('penerimaans', [
        'opd_id' => $opd->id,
        'rekening_id' => null,
    ]);
});

test('nullable rekening_id is still allowed for pengeluaran', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $this->actingAs($admin)
        ->post('/pengeluaran', [
            'opd_id' => $opd->id,
            'rekening_id' => null,
            'sumber_dana_id' => $sumberDana->id,
            'anggaran' => 500000,
            'realisasi' => 200000,
        ])
        ->assertSessionHasNoErrors('rekening_id');

    $this->assertDatabaseHas('pengeluarans', [
        'opd_id' => $opd->id,
        'rekening_id' => null,
    ]);
});

test('nonexistent rekening_id is rejected for penerimaan', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $this->actingAs($admin)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => 99999,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('rekening_id');
});
