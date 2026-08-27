<?php

use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Program;
use App\Models\User;

test('opd user only sees their own opd kegiatan', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);

    $programA = Program::create(['kode_program' => '1.1', 'nama_program' => 'Program A']);
    $programB = Program::create(['kode_program' => '2.2', 'nama_program' => 'Program B']);

    Kegiatan::create(['program_id' => $programA->id, 'opd_id' => $opdA->id, 'kode_kegiatan' => '1.1.1', 'nama_kegiatan' => 'Kegiatan A', 'pagu' => 1000000, 'realisasi' => 0]);
    Kegiatan::create(['program_id' => $programB->id, 'opd_id' => $opdB->id, 'kode_kegiatan' => '2.2.1', 'nama_kegiatan' => 'Kegiatan B', 'pagu' => 2000000, 'realisasi' => 0]);

    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->get('/program-kegiatan')
        ->assertSuccessful()
        ->assertSee('Kegiatan A')
        ->assertDontSee('Kegiatan B');
});

test('opd user cannot edit program of another opd', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);

    $programB = Program::create(['kode_program' => '2.2', 'nama_program' => 'Program B']);
    Kegiatan::create(['program_id' => $programB->id, 'opd_id' => $opdB->id, 'kode_kegiatan' => '2.2.1', 'nama_kegiatan' => 'Kegiatan B', 'pagu' => 1000000, 'realisasi' => 0]);

    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->get("/program-kegiatan/{$programB->id}/edit")
        ->assertForbidden();
});

test('opd user store forces their own opd', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);

    $program = Program::create(['kode_program' => '1.1', 'nama_program' => 'Program A']);
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->post("/program-kegiatan/{$program->id}/kegiatan", [
            'opd_id' => $opdB->id,
            'kode_kegiatan' => '1.1.1',
            'nama_kegiatan' => 'Kegiatan A',
            'pagu' => 1000000,
            'realisasi' => 0,
        ]);

    $this->assertDatabaseHas('kegiatan', [
        'nama_kegiatan' => 'Kegiatan A',
        'opd_id' => $opdA->id,
    ]);
});

test('admin sees all kegiatan', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);

    $programA = Program::create(['kode_program' => '1.1', 'nama_program' => 'Program A']);
    $programB = Program::create(['kode_program' => '2.2', 'nama_program' => 'Program B']);

    Kegiatan::create(['program_id' => $programA->id, 'opd_id' => $opdA->id, 'kode_kegiatan' => '1.1.1', 'nama_kegiatan' => 'Kegiatan A', 'pagu' => 1000000, 'realisasi' => 0]);
    Kegiatan::create(['program_id' => $programB->id, 'opd_id' => $opdB->id, 'kode_kegiatan' => '2.2.1', 'nama_kegiatan' => 'Kegiatan B', 'pagu' => 2000000, 'realisasi' => 0]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/program-kegiatan')
        ->assertSuccessful()
        ->assertSee('Kegiatan A')
        ->assertSee('Kegiatan B');
});
