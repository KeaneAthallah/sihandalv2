<?php

use App\Models\Opd;
use App\Models\SumberDana;
use App\Models\User;

test('opd user only sees their own opd sumber dana', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);

    SumberDana::create(['opd_id' => $opdA->id, 'nama_sumber_dana' => 'DAU', 'pagu' => 1000000, 'realisasi' => 0]);
    SumberDana::create(['opd_id' => $opdB->id, 'nama_sumber_dana' => 'DAK', 'pagu' => 2000000, 'realisasi' => 0]);

    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->get('/sumber-dana')
        ->assertSuccessful()
        ->assertSee('DAU')
        ->assertDontSee('DAK');
});

test('opd user cannot edit another opd sumber dana', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);

    $sumberDanaB = SumberDana::create(['opd_id' => $opdB->id, 'nama_sumber_dana' => 'DAK', 'pagu' => 1000000, 'realisasi' => 0]);

    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->get("/sumber-dana/{$sumberDanaB->id}/edit")
        ->assertForbidden();
});

test('opd user store forces their own opd', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);

    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->from('/sumber-dana/create')
        ->post('/sumber-dana', [
            'opd_id' => $opdB->id,
            'nama_sumber_dana' => 'DAU',
            'pagu' => 1000000,
            'realisasi' => 0,
        ]);

    $this->assertDatabaseHas('sumber_danas', [
        'nama_sumber_dana' => 'DAU',
        'opd_id' => $opdA->id,
    ]);
});

test('admin sees all sumber dana', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);

    SumberDana::create(['opd_id' => $opdA->id, 'nama_sumber_dana' => 'DAU', 'pagu' => 1000000, 'realisasi' => 0]);
    SumberDana::create(['opd_id' => $opdB->id, 'nama_sumber_dana' => 'DAK', 'pagu' => 2000000, 'realisasi' => 0]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/sumber-dana')
        ->assertSuccessful()
        ->assertSee('DAU')
        ->assertSee('DAK');
});
