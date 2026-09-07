<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\PermintaanDana;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\User;

function buildBelanjaFixture(array $overrides = []): array
{
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);

    $program = Program::create(['kode_program' => '1.2', 'nama_program' => 'Program A', 'opd_id' => $opd->id]);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'kode_kegiatan' => '1.2.3', 'nama_kegiatan' => 'Kegiatan A', 'pagu' => 0, 'realisasi' => 0,
    ]);
    $sub = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id, 'kode_sub_kegiatan' => '1.2.3.1', 'nama_sub_kegiatan' => 'Sub 1',
        'pagu' => 0, 'realisasi' => 0,
    ]);

    $belanja = Belanja::create(array_merge([
        'sub_kegiatan_id' => $sub->id,
        'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id,
        'opd_id' => $opd->id,
        'pagu' => 1000000,
        'realisasi' => 0,
        'dana_di_commit' => 0,
    ], $overrides));

    return compact('opd', 'opdB', 'sumberDana', 'rekening', 'program', 'kegiatan', 'sub', 'belanja');
}

test('belanja commit cannot exceed pagu when funds are already committed and realized', function () {
    ['belanja' => $belanja] = buildBelanjaFixture();

    $belanja->commit(300000);
    $belanja->realize(300000);
    $belanja->commit(400000);

    expect((float) $belanja->fresh()->dana_di_commit)->toBe(400000.0)
        ->and((float) $belanja->fresh()->realisasi)->toBe(300000.0)
        ->and((float) $belanja->fresh()->availablePagu())->toBe(300000.0);

    expect(fn () => $belanja->commit(300001))->toThrow(RuntimeException::class, 'melebihi pagu');
});

test('belanja commit does not mutate state when it throws', function () {
    ['belanja' => $belanja] = buildBelanjaFixture();

    expect(fn () => $belanja->commit(1500000))->toThrow(RuntimeException::class);

    expect((float) $belanja->fresh()->dana_di_commit)->toBe(0.0);
});

test('belanja releaseCommit never drives dana_di_commit below zero', function () {
    ['belanja' => $belanja] = buildBelanjaFixture();

    $belanja->commit(400000);
    $belanja->releaseCommit(999999999);

    expect((float) $belanja->fresh()->dana_di_commit)->toBe(0.0);
});

test('belanja update cannot reduce pagu below committed plus realized', function () {
    ['belanja' => $belanja, 'sub' => $sub, 'rekening' => $rekening, 'sumberDana' => $sumberDana, 'opd' => $opd] = buildBelanjaFixture([
        'pagu' => 1000000, 'realisasi' => 200000, 'dana_di_commit' => 300000,
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from("/sub-kegiatan/{$sub->id}/belanja/{$belanja->id}/edit")
        ->put("/sub-kegiatan/{$sub->id}/belanja/{$belanja->id}", [
            'sub_kegiatan_id' => $sub->id,
            'rekening_id' => $rekening->id,
            'sumber_dana_id' => $sumberDana->id,
            'opd_id' => $opd->id,
            'pagu' => 400000,
            'realisasi' => 200000,
        ])
        ->assertSessionHasErrors('pagu');

    $this->assertDatabaseHas('belanjas', ['id' => $belanja->id, 'pagu' => 1000000]);
});

test('belanja with committed funds cannot be deleted', function () {
    ['belanja' => $belanja, 'sub' => $sub] = buildBelanjaFixture(['dana_di_commit' => 300000]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete("/sub-kegiatan/{$sub->id}/belanja/{$belanja->id}")
        ->assertSessionHasErrors('belanja');

    $this->assertDatabaseHas('belanjas', ['id' => $belanja->id]);
});

test('belanja with realized funds cannot be deleted', function () {
    ['belanja' => $belanja, 'sub' => $sub] = buildBelanjaFixture(['realisasi' => 200000]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete("/sub-kegiatan/{$sub->id}/belanja/{$belanja->id}")
        ->assertSessionHasErrors('belanja');

    $this->assertDatabaseHas('belanjas', ['id' => $belanja->id]);
});

test('belanja without committed or realized funds can be deleted', function () {
    ['belanja' => $belanja, 'sub' => $sub] = buildBelanjaFixture();

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete("/sub-kegiatan/{$sub->id}/belanja/{$belanja->id}");

    $this->assertDatabaseMissing('belanjas', ['id' => $belanja->id]);
});

test('permintaan submit fails gracefully when jumlah exceeds available pagu belanja', function () {
    ['belanja' => $belanja, 'opd' => $opd, 'sumberDana' => $sumberDana] = buildBelanjaFixture();

    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0100/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'belanja_id' => $belanja->id,
        'jumlah' => 1500000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user)
        ->post("/permintaan-dana/{$permintaan->id}/submit")
        ->assertSessionHasErrors('jumlah');

    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'draft']);
    $this->assertDatabaseHas('belanjas', ['id' => $belanja->id, 'dana_di_commit' => 0]);
});

test('permintaan submit fails when belanja belongs to another opd', function () {
    ['belanja' => $belanja, 'opd' => $opd, 'sumberDana' => $sumberDana, 'opdB' => $opdB] = buildBelanjaFixture();

    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0101/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'belanja_id' => $belanja->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $belanja->update(['opd_id' => $opdB->id]);

    $this->actingAs($userA)
        ->post("/permintaan-dana/{$permintaan->id}/submit")
        ->assertSessionHasErrors('jumlah');

    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'draft']);
});

test('permintaan submit succeeds and commits funds from belanja', function () {
    ['belanja' => $belanja, 'opd' => $opd, 'sumberDana' => $sumberDana] = buildBelanjaFixture();

    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0102/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'belanja_id' => $belanja->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user)
        ->post("/permintaan-dana/{$permintaan->id}/submit");

    $this->assertDatabaseHas('permintaan_danas', ['id' => $permintaan->id, 'status' => 'menunggu']);
    $this->assertDatabaseHas('belanjas', ['id' => $belanja->id, 'dana_di_commit' => 400000]);
});

test('next permintaan number does not reuse numbers after deletion', function () {
    ['opd' => $opd, 'sumberDana' => $sumberDana] = buildBelanjaFixture();

    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $year = now()->year;

    $this->actingAs($user)->post('/permintaan-dana', [
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'jumlah' => 500000,
        'keperluan' => 'Permintaan A',
    ]);

    $this->actingAs($user)->post('/permintaan-dana', [
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'jumlah' => 500000,
        'keperluan' => 'Permintaan B',
    ]);

    $first = PermintaanDana::where('keperluan', 'Permintaan A')->firstOrFail();
    $this->actingAs($user)->delete("/permintaan-dana/{$first->id}");

    $this->actingAs($user)->post('/permintaan-dana', [
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'jumlah' => 500000,
        'keperluan' => 'Permintaan C',
    ]);

    $this->assertDatabaseHas('permintaan_danas', [
        'keperluan' => 'Permintaan B',
        'nomor_permintaan' => "PD-0002/{$year}",
    ]);

    $this->assertDatabaseHas('permintaan_danas', [
        'keperluan' => 'Permintaan C',
        'nomor_permintaan' => "PD-0003/{$year}",
    ]);
});

test('permintaan store assigns the active fiscal year', function () {
    ['opd' => $opd, 'sumberDana' => $sumberDana] = buildBelanjaFixture();

    $ta = TahunAnggaran::create([
        'tahun' => (string) now()->year,
        'tanggal_mulai' => now()->startOfYear(),
        'tanggal_selesai' => now()->endOfYear(),
        'status' => 'open',
        'is_active' => true,
    ]);

    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user)->post('/permintaan-dana', [
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'jumlah' => 500000,
        'keperluan' => 'Operasional tahun berjalan',
    ]);

    $this->assertDatabaseHas('permintaan_danas', [
        'keperluan' => 'Operasional tahun berjalan',
        'tahun_anggaran_id' => $ta->id,
    ]);
});
