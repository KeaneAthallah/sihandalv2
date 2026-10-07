<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\PermintaanDana;
use App\Models\Persetujuan;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

uses()->group('api');

function apiWorkflowFixture(): array
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

    $belanja = Belanja::create([
        'sub_kegiatan_id' => $sub->id,
        'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id,
        'opd_id' => $opd->id,
        'pagu' => 1000000,
        'realisasi' => 0,
        'dana_di_commit' => 0,
    ]);

    // Kas masuk agar permintaan dana dapat dikomit
    // pada uji-coba yang menjalankan submit.
    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'target' => 1000000,
    ]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDana->id,
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);

    return compact('opd', 'opdB', 'sumberDana', 'belanja', 'program', 'kegiatan', 'sub');
}

test('opd user creates permintaan as draft with auto number', function (): void {
    ['opd' => $opd, 'sumberDana' => $sumberDana, 'kegiatan' => $kegiatan, 'sub' => $sub, 'belanja' => $belanja] = apiWorkflowFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/permintaan-dana', [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'kegiatan_id' => $kegiatan->id,
            'sub_kegiatan_id' => $sub->id,
            'belanja_id' => $belanja->id,
            'jumlah' => 400000,
            'keperluan' => 'Operasional',
        ])
        ->assertStatus(201)
        ->assertJson(fn (AssertableJson $json) => $json->where('success', true)->etc());

    $permintaan = PermintaanDana::findOrFail($response->json('data.id'));

    expect($permintaan->status)->toBe('draft')
        ->and($permintaan->nomor_permintaan)->toMatch('/^PD-\\d{4}\\/'.now()->year.'$/');
});

test('opd user cannot create permintaan for another opd', function (): void {
    ['opd' => $opd, 'opdB' => $opdB, 'sumberDana' => $sumberDana] = apiWorkflowFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/permintaan-dana', [
            'opd_id' => $opdB->id,
            'sumber_dana_id' => $sumberDana->id,
            'jumlah' => 400000,
            'keperluan' => 'Operasional',
        ])
        ->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json->has('errors.opd_id')->etc());

    expect(PermintaanDana::count())->toBe(0);
});

test('submit transitions draft to menunggu and commits belanja funds', function (): void {
    ['opd' => $opd, 'sumberDana' => $sumberDana, 'belanja' => $belanja] = apiWorkflowFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'belanja_id' => $belanja->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/permintaan-dana/{$permintaan->id}/submit")
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('data.status', 'menunggu')->etc());

    expect($permintaan->fresh()->status)->toBe('menunggu')
        ->and((float) $belanja->fresh()->dana_di_commit)->toBe(400000.0);
});

test('submit beyond available pagu fails gracefully with 422 and no commit', function (): void {
    ['opd' => $opd, 'sumberDana' => $sumberDana, 'belanja' => $belanja] = apiWorkflowFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'belanja_id' => $belanja->id,
        'jumlah' => 1500000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/permintaan-dana/{$permintaan->id}/submit")
        ->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('success', false)
            ->where('message', 'Unable to perform operation')
            ->has('errors.business')
            ->etc());

    expect($permintaan->fresh()->status)->toBe('draft')
        ->and((float) $belanja->fresh()->dana_di_commit)->toBe(0.0);
});

test('double submit does not change an already submitted permintaan', function (): void {
    ['opd' => $opd, 'sumberDana' => $sumberDana, 'kegiatan' => $kegiatan, 'sub' => $sub, 'belanja' => $belanja] = apiWorkflowFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'kegiatan_id' => $kegiatan->id,
        'sub_kegiatan_id' => $sub->id,
        'belanja_id' => $belanja->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user, 'sanctum')->postJson("/api/v1/permintaan-dana/{$permintaan->id}/submit")->assertOk();
    $this->actingAs($user, 'sanctum')->postJson("/api/v1/permintaan-dana/{$permintaan->id}/submit")->assertStatus(422);

    expect($permintaan->fresh()->status)->toBe('menunggu');
});

test('approve realizes funds and records persetujuan once', function (): void {
    ['opd' => $opd, 'sumberDana' => $sumberDana, 'belanja' => $belanja] = apiWorkflowFixture();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'belanja_id' => $belanja->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/permintaan-dana/{$permintaan->id}/approve")
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('data.status', 'disetujui')->etc());

    expect($permintaan->fresh()->status)->toBe('disetujui')
        ->and((float) $belanja->fresh()->realisasi)->toBe(400000.0)
        ->and((float) $belanja->fresh()->dana_di_commit)->toBe(0.0)
        ->and(Persetujuan::where('permintaan_dana_id', $permintaan->id)->count())->toBe(1);

    // Second approve is rejected.
    $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/permintaan-dana/{$permintaan->id}/approve")
        ->assertStatus(422);

    expect(Persetujuan::where('permintaan_dana_id', $permintaan->id)->count())->toBe(1);
});

test('opd user cannot approve or reject permintaan', function (): void {
    ['opd' => $opd, 'sumberDana' => $sumberDana] = apiWorkflowFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/permintaan-dana/{$permintaan->id}/approve")
        ->assertForbidden();

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/permintaan-dana/{$permintaan->id}/reject")
        ->assertForbidden();

    expect($permintaan->fresh()->status)->toBe('menunggu');
});

test('reject releases committed funds and records the rejection', function (): void {
    ['opd' => $opd, 'sumberDana' => $sumberDana, 'belanja' => $belanja] = apiWorkflowFixture();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'belanja_id' => $belanja->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    // Submit commits 400k.
    $this->actingAs($user, 'sanctum')->postJson("/api/v1/permintaan-dana/{$permintaan->id}/submit")->assertOk();
    expect((float) $belanja->fresh()->dana_di_commit)->toBe(400000.0);

    // Reject releases it back.
    $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/permintaan-dana/{$permintaan->id}/reject")
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('data.status', 'ditolak')->etc());

    expect($permintaan->fresh()->status)->toBe('ditolak')
        ->and((float) $belanja->fresh()->dana_di_commit)->toBe(0.0)
        ->and((float) $belanja->fresh()->realisasi)->toBe(0.0)
        ->and(Persetujuan::where('permintaan_dana_id', $permintaan->id)->where('keputusan', 'ditolak')->count())->toBe(1);
});

test('clients cannot set status disetujui through generic PATCH', function (): void {
    ['opd' => $opd, 'sumberDana' => $sumberDana, 'kegiatan' => $kegiatan, 'sub' => $sub, 'belanja' => $belanja] = apiWorkflowFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'kegiatan_id' => $kegiatan->id,
        'sub_kegiatan_id' => $sub->id,
        'belanja_id' => $belanja->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/v1/permintaan-dana/{$permintaan->id}", [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'kegiatan_id' => $kegiatan->id,
            'sub_kegiatan_id' => $sub->id,
            'belanja_id' => $belanja->id,
            'jumlah' => 400000,
            'keperluan' => 'Operasional diubah',
            'status' => 'disetujui',
        ])
        ->assertOk();

    // Status untouched; update only allowed for draft/ditolak anyway.
    expect($permintaan->fresh()->status)->toBe('draft')
        ->and($permintaan->fresh()->keperluan)->toBe('Operasional diubah');
});

test('permintaan cannot be edited after submission', function (): void {
    ['opd' => $opd, 'sumberDana' => $sumberDana, 'kegiatan' => $kegiatan, 'sub' => $sub, 'belanja' => $belanja] = apiWorkflowFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'kegiatan_id' => $kegiatan->id,
        'sub_kegiatan_id' => $sub->id,
        'belanja_id' => $belanja->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/permintaan-dana/{$permintaan->id}", [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'kegiatan_id' => $kegiatan->id,
            'sub_kegiatan_id' => $sub->id,
            'belanja_id' => $belanja->id,
            'jumlah' => 999000,
            'keperluan' => 'Diubah',
        ])
        ->assertStatus(422);

    expect((float) $permintaan->fresh()->jumlah)->toBe(400000.0);
});

test('opd user cannot access another opd permintaan', function (): void {
    ['opd' => $opd, 'opdB' => $opdB, 'sumberDana' => $sumberDana] = apiWorkflowFixture();
    $userB = User::factory()->create(['role' => 'opd', 'opd_id' => $opdB->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($userB, 'sanctum')
        ->getJson("/api/v1/permintaan-dana/{$permintaan->id}")
        ->assertForbidden();
});

test('persetujuan listing is scoped and shows recorded decisions', function (): void {
    ['opd' => $opd, 'sumberDana' => $sumberDana] = apiWorkflowFixture();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($admin, 'sanctum')->postJson("/api/v1/permintaan-dana/{$permintaan->id}/approve")->assertOk();

    $data = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/permintaan-dana/{$permintaan->id}/persetujuan")
        ->assertOk()
        ->json('data');

    expect(count($data))->toBe(1)
        ->and($data[0]['keputusan'])->toBe('disetujui');
});
