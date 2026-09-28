<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

uses()->group('api');

function apiBelanjaFixture(): array
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

    return compact('opd', 'opdB', 'sumberDana', 'rekening', 'program', 'kegiatan', 'sub', 'belanja');
}

test('commit endpoint moves available pagu into dana_di_commit', function (): void {
    ['belanja' => $belanja, 'opd' => $opd] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/belanja/{$belanja->id}/commit", ['amount' => 300000])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('success', true)->etc());

    expect((float) $belanja->fresh()->dana_di_commit)->toBe(300000.0)
        ->and((float) $belanja->fresh()->availablePagu())->toBe(700000.0);
});

test('commit beyond available pagu returns 422 without mutating state', function (): void {
    ['belanja' => $belanja, 'opd' => $opd] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/belanja/{$belanja->id}/commit", ['amount' => 1500000])
        ->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('success', false)
            ->where('message', 'Unable to perform operation')
            ->has('errors.business')
            ->etc());

    expect((float) $belanja->fresh()->dana_di_commit)->toBe(0.0);
});

test('release returns committed funds to available pagu', function (): void {
    ['belanja' => $belanja, 'opd' => $opd] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $belanja->commit(400000);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/belanja/{$belanja->id}/release", ['amount' => 250000])
        ->assertOk();

    expect((float) $belanja->fresh()->dana_di_commit)->toBe(150000.0)
        ->and((float) $belanja->fresh()->availablePagu())->toBe(850000.0);
});

test('release never drives dana_di_commit below zero', function (): void {
    ['belanja' => $belanja, 'opd' => $opd] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $belanja->commit(100000);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/belanja/{$belanja->id}/release", ['amount' => 999999999])
        ->assertOk();

    expect((float) $belanja->fresh()->dana_di_commit)->toBe(0.0);
});

test('realize records realization and consumes commit first', function (): void {
    ['belanja' => $belanja, 'opd' => $opd] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $belanja->commit(400000);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/belanja/{$belanja->id}/realize", ['amount' => 300000])
        ->assertOk();

    expect((float) $belanja->fresh()->realisasi)->toBe(300000.0)
        ->and((float) $belanja->fresh()->dana_di_commit)->toBe(100000.0);
});

test('realize cannot exceed available pagu', function (): void {
    ['belanja' => $belanja, 'opd' => $opd] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/belanja/{$belanja->id}/realize", ['amount' => 1200000])
        ->assertStatus(422);

    expect((float) $belanja->fresh()->realisasi)->toBe(0.0);
});

test('financial actions reject non-positive amounts', function (): void {
    ['belanja' => $belanja, 'opd' => $opd] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/belanja/{$belanja->id}/commit", ['amount' => 0])
        ->assertStatus(422);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/belanja/{$belanja->id}/commit", ['amount' => -5])
        ->assertStatus(422);

    expect((float) $belanja->fresh()->dana_di_commit)->toBe(0.0);
});

test('opd user cannot commit another opd belanja', function (): void {
    ['belanja' => $belanja, 'opdB' => $opdB] = apiBelanjaFixture();
    $userB = User::factory()->create(['role' => 'opd', 'opd_id' => $opdB->id]);

    $this->actingAs($userB, 'sanctum')
        ->postJson("/api/v1/belanja/{$belanja->id}/commit", ['amount' => 100000])
        ->assertForbidden();

    expect((float) $belanja->fresh()->dana_di_commit)->toBe(0.0);
});

test('unauthenticated financial actions are rejected', function (): void {
    ['belanja' => $belanja] = apiBelanjaFixture();

    $this->postJson("/api/v1/belanja/{$belanja->id}/commit", ['amount' => 1000])
        ->assertStatus(401);
});

test('belanja update cannot reduce historical realization', function (): void {
    ['belanja' => $belanja, 'opd' => $opd] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $belanja->update(['realisasi' => 200000]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/belanja/{$belanja->id}", [
            'rekening_id' => $belanja->rekening_id,
            'pagu' => 1000000,
            'realisasi' => 100000,
        ])
        ->assertStatus(422);

    expect((float) $belanja->fresh()->realisasi)->toBe(200000.0);
});

test('belanja update cannot set pagu below commit plus realization', function (): void {
    ['belanja' => $belanja, 'opd' => $opd] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $belanja->update(['dana_di_commit' => 300000, 'realisasi' => 200000]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/belanja/{$belanja->id}", [
            'rekening_id' => $belanja->rekening_id,
            'pagu' => 400000,
            'realisasi' => 200000,
        ])
        ->assertStatus(422);

    expect((float) $belanja->fresh()->pagu)->toBe(1000000.0);
});

test('belanja resource exposes available_pagu but collections stay light', function (): void {
    ['belanja' => $belanja, 'opd' => $opd] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/belanja/{$belanja->id}")
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('success', true)
            ->where('data.pagu', 1000000)
            ->where('data.available_pagu', 1000000)
            ->etc());
});

test('belanja store rejects sub kegiatan from another opd', function (): void {
    ['opd' => $opd, 'opdB' => $opdB, 'rekening' => $rekening, 'sub' => $sub] = apiBelanjaFixture();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/belanja', [
            'sub_kegiatan_id' => $sub->id,
            'rekening_id' => $rekening->id,
            'opd_id' => $opdB->id,
            'pagu' => 500000,
        ])
        ->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json->where('success', false)->etc());
});
