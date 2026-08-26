<?php

use App\Models\Opd;
use App\Models\PermintaanDana;
use App\Models\SumberDana;
use App\Models\User;

test('opd user can create permintaan from their opd sumber dana', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['opd_id' => $opd->id, 'nama_sumber_dana' => 'DAU', 'pagu' => 1000000, 'realisasi' => 0]);

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

test('permintaan exceeding available pagu is rejected', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['opd_id' => $opd->id, 'nama_sumber_dana' => 'DAU', 'pagu' => 1000000, 'realisasi' => 0]);

    $this->actingAs($user)
        ->from('/permintaan-dana/create')
        ->post('/permintaan-dana', [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'jumlah' => 1500000,
            'keperluan' => 'Kebutuhan operasional',
        ])
        ->assertSessionHasErrors('jumlah');

    $this->assertDatabaseCount('permintaan_danas', 0);
});

test('opd user cannot use sumber dana from another opd', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);
    $sumberDanaB = SumberDana::create(['opd_id' => $opdB->id, 'nama_sumber_dana' => 'DAK', 'pagu' => 1000000, 'realisasi' => 0]);

    $this->actingAs($user)
        ->from('/permintaan-dana/create')
        ->post('/permintaan-dana', [
            'opd_id' => $opdA->id,
            'sumber_dana_id' => $sumberDanaB->id,
            'jumlah' => 500000,
            'keperluan' => 'Kebutuhan operasional',
        ])
        ->assertSessionHasErrors('sumber_dana_id');

    $this->assertDatabaseCount('permintaan_danas', 0);
});

test('submitting permintaan commits dana temporarily', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['opd_id' => $opd->id, 'nama_sumber_dana' => 'DAU', 'pagu' => 1000000, 'realisasi' => 0]);

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
    $this->assertSame(400000.0, (float) $sumberDana->fresh()->dana_di_commit);
    $this->assertSame(0.0, (float) $sumberDana->fresh()->realisasi);
});

test('approving permintaan realizes the funds', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->admin()->create();
    $sumberDana = SumberDana::create(['opd_id' => $opd->id, 'nama_sumber_dana' => 'DAU', 'pagu' => 1000000, 'realisasi' => 0]);
    $sumberDana->commit(400000);

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
        ->post("/persetujuan/{$permintaan->id}/setujui");

    $sumberDana->refresh();
    $this->assertSame(400000.0, (float) $sumberDana->realisasi);
    $this->assertSame(0.0, (float) $sumberDana->dana_di_commit);
});

test('rejecting permintaan releases the commit', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->admin()->create();
    $sumberDana = SumberDana::create(['opd_id' => $opd->id, 'nama_sumber_dana' => 'DAU', 'pagu' => 1000000, 'realisasi' => 0]);
    $sumberDana->commit(400000);

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
        ->post("/persetujuan/{$permintaan->id}/tolak");

    $sumberDana->refresh();
    $this->assertSame(0.0, (float) $sumberDana->dana_di_commit);
    $this->assertSame(0.0, (float) $sumberDana->realisasi);
    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'ditolak']);
});

test('committed dana reduces availability for a second request', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['opd_id' => $opd->id, 'nama_sumber_dana' => 'DAU', 'pagu' => 1000000, 'realisasi' => 0]);

    $first = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/2026',
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 700000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user)->post("/permintaan-dana/{$first->id}/submit");

    $this->actingAs($user)
        ->from('/permintaan-dana/create')
        ->post('/permintaan-dana', [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'jumlah' => 400000,
            'keperluan' => 'Operasional 2',
        ])
        ->assertSessionHasErrors('jumlah');

    $this->assertDatabaseCount('permintaan_danas', 1);
});
