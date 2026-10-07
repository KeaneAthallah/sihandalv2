<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\PermintaanDana;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\User;

test('opd user can create permintaan from a global sumber dana', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);

    $program = Program::create(['kode_program' => '1.1', 'nama_program' => 'Program A', 'opd_id' => $opd->id]);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id, 'kode_kegiatan' => '1.1.1', 'nama_kegiatan' => 'Kegiatan A',
    ]);
    $subKegiatan = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id, 'kode_sub_kegiatan' => '1.1.1.1', 'nama_sub_kegiatan' => 'Sub A',
    ]);
    $belanja = Belanja::create([
        'sub_kegiatan_id' => $subKegiatan->id, 'rekening_id' => $rekening->id, 'sumber_dana_id' => $sumberDana->id,
        'opd_id' => $opd->id, 'pagu' => 1000000, 'realisasi' => 0, 'dana_di_commit' => 0,
    ]);

    $this->actingAs($user)
        ->from('/permintaan-dana/create')
        ->post('/permintaan-dana', [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'kegiatan_id' => $kegiatan->id,
            'sub_kegiatan_id' => $subKegiatan->id,
            'belanja_id' => $belanja->id,
            'jumlah' => 500000,
            'keperluan' => 'Kebutuhan operasional',
        ]);

    $this->assertDatabaseHas('permintaan_danas', [
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'belanja_id' => $belanja->id,
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
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);

    $program = Program::create(['kode_program' => '1.1', 'nama_program' => 'Program A', 'opd_id' => $opd->id]);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id, 'kode_kegiatan' => '1.1.1', 'nama_kegiatan' => 'Kegiatan A',
    ]);
    $subKegiatan = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id, 'kode_sub_kegiatan' => '1.1.1.1', 'nama_sub_kegiatan' => 'Sub A',
    ]);
    $belanja = Belanja::create([
        'sub_kegiatan_id' => $subKegiatan->id, 'rekening_id' => $rekening->id, 'sumber_dana_id' => $sumberDana->id,
        'opd_id' => $opd->id, 'pagu' => 1000000, 'realisasi' => 0, 'dana_di_commit' => 0,
    ]);

    // Kas masuk agar permintaan dana dapat
    // dikomit (tersedia = min(pagu, kas)).
    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'target' => 1000000,
    ]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDana->id,
        'realisasi' => 400000,
        'tanggal' => now(),
    ]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/2026',
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'kegiatan_id' => $kegiatan->id,
        'sub_kegiatan_id' => $subKegiatan->id,
        'belanja_id' => $belanja->id,
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
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);

    $program = Program::create(['kode_program' => '1.1', 'nama_program' => 'Program A', 'opd_id' => $opd->id]);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id, 'kode_kegiatan' => '1.1.1', 'nama_kegiatan' => 'Kegiatan A',
    ]);
    $subKegiatan = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id, 'kode_sub_kegiatan' => '1.1.1.1', 'nama_sub_kegiatan' => 'Sub A',
    ]);
    $belanja = Belanja::create([
        'sub_kegiatan_id' => $subKegiatan->id, 'rekening_id' => $rekening->id, 'sumber_dana_id' => $sumberDana->id,
        'opd_id' => $opd->id, 'pagu' => 1000000, 'realisasi' => 0, 'dana_di_commit' => 0,
    ]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/2026',
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'kegiatan_id' => $kegiatan->id,
        'sub_kegiatan_id' => $subKegiatan->id,
        'belanja_id' => $belanja->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($user)
        ->from("/permintaan-dana/{$permintaan->id}/edit")
        ->put("/permintaan-dana/{$permintaan->id}", [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'kegiatan_id' => $kegiatan->id,
            'sub_kegiatan_id' => $subKegiatan->id,
            'belanja_id' => $belanja->id,
            'jumlah' => 500000,
            'keperluan' => 'Operasional diubah',
        ])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'jumlah' => 400000]);
});
