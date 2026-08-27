<?php

use App\Models\Opd;
use App\Models\PermintaanDana;
use App\Models\SumberDana;
use App\Models\User;

test('opd user can create permintaan from a global sumber dana', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $this->actingAs($user)
        ->from('/permintaan-dana/create')
        ->post('/permintaan-dana', [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'jumlah' => 500000,
            'keperluan' => 'Kebutuhan operasional',
        ]);

    $this->assertDatabaseHas('permintaan_danas', [
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'jumlah' => 500000,
        'sumber_dana' => 'DAU',
        'status' => 'draft',
    ]);
});

test('opd user cannot create permintaan for another opd', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $this->actingAs($user)
        ->from('/permintaan-dana/create')
        ->post('/permintaan-dana', [
            'opd_id' => $opdB->id,
            'sumber_dana_id' => $sumberDana->id,
            'jumlah' => 500000,
            'keperluan' => 'Kebutuhan operasional',
        ])
        ->assertSessionHasErrors('opd_id');

    $this->assertDatabaseCount('permintaan_danas', 0);
});

test('submitting a draft permintaan sets it to menunggu', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/2026',
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user)
        ->post("/permintaan-dana/{$permintaan->id}/submit");

    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'menunggu']);
});

test('approving permintaan records persetujuan and status disetujui', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $admin = User::factory()->admin()->create();
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/2026',
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($admin)
        ->post("/persetujuan/{$permintaan->id}/setujui");

    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'disetujui']);
    $this->assertDatabaseHas('persetujuans', [
        'permintaan_dana_id' => $permintaan->id,
        'keputusan' => 'disetujui',
    ]);
});

test('rejecting permintaan records persetujuan and status ditolak', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $admin = User::factory()->admin()->create();
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/2026',
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($admin)
        ->post("/persetujuan/{$permintaan->id}/tolak");

    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'ditolak']);
    $this->assertDatabaseHas('persetujuans', [
        'permintaan_dana_id' => $permintaan->id,
        'keputusan' => 'ditolak',
    ]);
});

test('permintaan cannot be edited after it is submitted', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/2026',
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($user)
        ->from("/permintaan-dana/{$permintaan->id}/edit")
        ->put("/permintaan-dana/{$permintaan->id}", [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'jumlah' => 500000,
            'keperluan' => 'Operasional diubah',
        ])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'jumlah' => 400000]);
});
