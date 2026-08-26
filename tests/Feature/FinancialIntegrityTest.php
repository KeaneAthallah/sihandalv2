<?php

use App\Models\Opd;
use App\Models\PermintaanDana;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\User;

test('double submission does not commit funds twice', function () {
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

    $this->actingAs($user)->post("/permintaan-dana/{$permintaan->id}/submit");
    $this->assertSame(400000.0, (float) $sumberDana->fresh()->dana_di_commit);

    $this->actingAs($user)->post("/permintaan-dana/{$permintaan->id}/submit");
    $this->assertSame(400000.0, (float) $sumberDana->fresh()->dana_di_commit);
    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'menunggu']);
});

test('double approval does not realize funds twice', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $admin = User::factory()->admin()->create();
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

    $this->actingAs($admin)->post("/persetujuan/{$permintaan->id}/setujui");
    $this->assertSame(400000.0, (float) $sumberDana->fresh()->realisasi);

    $this->actingAs($admin)->post("/persetujuan/{$permintaan->id}/setujui");
    $this->assertSame(400000.0, (float) $sumberDana->fresh()->realisasi);
});

test('double rejection does not release funds twice', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $admin = User::factory()->admin()->create();
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

    $this->actingAs($admin)->post("/persetujuan/{$permintaan->id}/tolak");
    $this->assertSame(0.0, (float) $sumberDana->fresh()->dana_di_commit);

    $this->actingAs($admin)->post("/persetujuan/{$permintaan->id}/tolak");
    $this->assertSame(0.0, (float) $sumberDana->fresh()->dana_di_commit);
});

test('fund limits are enforced correctly', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['opd_id' => $opd->id, 'nama_sumber_dana' => 'DAU', 'pagu' => 1000000, 'realisasi' => 0]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/2026',
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 1200000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user)->post("/permintaan-dana/{$permintaan->id}/submit");
    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'draft']);
    $this->assertSame(0.0, (float) $sumberDana->fresh()->dana_di_commit);
});

test('opd user cannot approve or reject permintaan dana', function () {
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
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $this->actingAs($admin)
        ->post('/sumber-dana', [
            'opd_id' => $opd->id,
            'nama_sumber_dana' => 'DAU',
            'pagu' => 1000000,
            'realisasi' => 0,
        ]);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'created',
        'auditable_type' => 'App\\Models\\SumberDana',
    ]);
});
