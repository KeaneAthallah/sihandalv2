<?php

use App\Models\Opd;
use App\Models\PermintaanDana;
use App\Models\Persetujuan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\User;

test('double submission does not change an already submitted permintaan', function () {
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

    $this->actingAs($user)->post("/permintaan-dana/{$permintaan->id}/submit");
    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'menunggu']);

    $this->actingAs($user)->post("/permintaan-dana/{$permintaan->id}/submit");
    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'menunggu']);
});

test('double approval only records persetujuan once', function () {
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

    $this->actingAs($admin)->post("/persetujuan/{$permintaan->id}/setujui");
    $this->actingAs($admin)->post("/persetujuan/{$permintaan->id}/setujui");

    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'disetujui']);
    expect(Persetujuan::where('permintaan_dana_id', $permintaan->id)->count())->toBe(1);
});

test('double rejection only records persetujuan once', function () {
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

    $this->actingAs($admin)->post("/persetujuan/{$permintaan->id}/tolak");
    $this->actingAs($admin)->post("/persetujuan/{$permintaan->id}/tolak");

    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'ditolak']);
    expect(Persetujuan::where('permintaan_dana_id', $permintaan->id)->count())->toBe(1);
});

test('permintaan with non-positive jumlah is rejected at creation', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $this->actingAs($user)
        ->from('/permintaan-dana/create')
        ->post('/permintaan-dana', [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'jumlah' => 0,
            'keperluan' => 'Operasional',
        ])
        ->assertSessionHasErrors('jumlah');

    $this->assertDatabaseCount('permintaan_danas', 0);
});

test('opd user cannot approve or reject permintaan dana', function () {
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

    $this->actingAs($user)->post("/persetujuan/{$permintaan->id}/setujui")->assertForbidden();
    $this->actingAs($user)->post("/persetujuan/{$permintaan->id}/tolak")->assertForbidden();
});

test('opd user cannot manage rekening kas', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user)->get('/rekening-kas/create')->assertForbidden();
    $this->actingAs($user)->post('/rekening-kas', [
        'kode' => '1.1.1',
        'nama' => 'Kas',
        'tipe' => 'kas',
        'saldo' => 0,
    ])->assertForbidden();
});

test('fiscal year can be created and activated', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/tahun-anggaran', [
            'tahun' => '2026',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
        ]);

    $this->assertDatabaseHas('tahun_anggarans', ['tahun' => '2026', 'status' => 'open']);

    $ta = TahunAnggaran::where('tahun', '2026')->first();
    $this->actingAs($admin)->post("/tahun-anggaran/{$ta->id}/activate");
    $this->assertTrue($ta->fresh()->is_active);
});

test('audit log is created for financial operations', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/sumber-dana', [
            'nama_sumber_dana' => 'DAU',
        ]);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'created',
        'auditable_type' => 'App\\Models\\SumberDana',
    ]);
});
