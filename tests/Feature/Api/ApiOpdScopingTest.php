<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\PosisiKas;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\RekeningBank;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TransferDana;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

uses()->group('api');

function apiScopeFixture(): array
{
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $sumberDanaB = SumberDana::create(['nama_sumber_dana' => 'DAK']);
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);
    $kasRekening = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Umum', 'tipe' => 'kas']);

    $program = Program::create(['kode_program' => '1.2', 'nama_program' => 'Program A', 'opd_id' => $opdA->id]);
    $kegiatanA = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opdA->id,
        'kode_kegiatan' => '1.2.3', 'nama_kegiatan' => 'Kegiatan A', 'pagu' => 0, 'realisasi' => 0,
    ]);
    $subA = SubKegiatan::create([
        'kegiatan_id' => $kegiatanA->id, 'kode_sub_kegiatan' => '1.2.3.1', 'nama_sub_kegiatan' => 'Sub A',
        'pagu' => 0, 'realisasi' => 0,
    ]);

    Belanja::create([
        'sub_kegiatan_id' => $subA->id, 'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id, 'opd_id' => $opdA->id,
        'pagu' => 1000000, 'realisasi' => 100000, 'dana_di_commit' => 50000,
    ]);

    Penerimaan::create([
        'opd_id' => $opdA->id, 'rekening_id' => $kasRekening->id,
        'target' => 500000,
    ]);

    Pengeluaran::create([
        'opd_id' => $opdA->id, 'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id, 'sumber_dana' => 'DAU',
        'jumlah' => 400000, 'keperluan' => 'Operasional',
    ]);

    PosisiKas::create([
        'opd_id' => $opdA->id,
        'tanggal' => now(),
        'nama_rekening' => 'Kas Umum',
        'nomor_rekening' => '001-000-1',
        'saldo' => 130000,
    ]);

    PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year, 'opd_id' => $opdA->id,
        'sumber_dana_id' => $sumberDana->id, 'sumber_dana' => 'DAU',
        'jumlah' => 100000, 'keperluan' => 'Operasional', 'status' => 'menunggu',
    ]);

    TransferDana::create([
        'nomor_transfer' => 'TF-0001/'.now()->year, 'opd_id' => $opdA->id,
        'jumlah' => 50000,
        'sumber_dana_pengirim_id' => $sumberDanaB->id,
        'sumber_dana_penerima_id' => $sumberDana->id,
        'status' => 'draft',
    ]);

    RekeningBank::create([
        'bank_name' => 'Bank Global', 'account_number' => '777-000-1',
        'account_name' => 'Global Master', 'is_active' => true,
    ]);

    return compact('opdA', 'opdB', 'sumberDana', 'rekening', 'kasRekening');
}

test('opd users only see their own opds in the opd list', function (): void {
    ['opdA' => $opdA] = apiScopeFixture();
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $data = $this->actingAs($userA, 'sanctum')
        ->getJson('/api/v1/opds')
        ->assertOk()
        ->json('data');

    expect(count($data))->toBe(1)
        ->and($data[0]['id'])->toBe($opdA->id);
});

test('admins see all opds', function (): void {
    apiScopeFixture();
    $admin = User::factory()->admin()->create();

    $data = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/opds')
        ->assertOk()
        ->json('data');

    expect(count($data))->toBe(2);
});

test('belanja list is opd scoped', function (): void {
    ['opdA' => $opdA] = apiScopeFixture();
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);
    $admin = User::factory()->admin()->create();

    $userBelanja = $this->actingAs($userA, 'sanctum')->getJson('/api/v1/belanja')->json('meta.total');
    $adminBelanja = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/belanja')->json('meta.total');

    expect($userBelanja)->toBe(1)
        ->and($adminBelanja)->toBe(1);
});

test('pengeluaran list is opd scoped', function (): void {
    ['opdA' => $opdA] = apiScopeFixture();
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    expect($this->actingAs($userA, 'sanctum')->getJson('/api/v1/pengeluaran')->json('meta.total'))->toBe(1);
});

test('posisi kas list is opd scoped', function (): void {
    ['opdA' => $opdA] = apiScopeFixture();
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    expect($this->actingAs($userA, 'sanctum')->getJson('/api/v1/posisi-kas')->json('meta.total'))->toBe(1);
});

test('permintaan dana list is opd scoped', function (): void {
    ['opdA' => $opdA] = apiScopeFixture();
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    expect($this->actingAs($userA, 'sanctum')->getJson('/api/v1/permintaan-dana')->json('meta.total'))->toBe(1);
});

test('transfer dana list is opd scoped', function (): void {
    ['opdA' => $opdA] = apiScopeFixture();
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    expect($this->actingAs($userA, 'sanctum')->getJson('/api/v1/transfer-dana')->json('meta.total'))->toBe(1);
});

test('rekening banks are global and not opd scoped', function (): void {
    ['opdB' => $opdB] = apiScopeFixture();
    $userB = User::factory()->create(['role' => 'opd', 'opd_id' => $opdB->id]);

    $data = $this->actingAs($userB, 'sanctum')
        ->getJson('/api/v1/rekening-banks')
        ->assertOk()
        ->json('data');

    // The global bank master is visible to any authenticated user.
    expect(count($data))->toBeGreaterThanOrEqual(1);
});

test('audit logs are admin only', function (): void {
    apiScopeFixture();
    $userA = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($userA, 'sanctum')
        ->getJson('/api/v1/audit-logs')
        ->assertForbidden();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/audit-logs')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('success', true)->etc());
});

test('dashboard respects opd scoping', function (): void {
    ['opdA' => $opdA] = apiScopeFixture();
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $data = $this->actingAs($userA, 'sanctum')
        ->getJson('/api/v1/dashboard')
        ->assertOk()
        ->json('data');

    expect($data['total_pagu'])->toBe(1000000)
        ->and($data['total_realisasi'])->toBe(100000)
        ->and($data['total_commit'])->toBe(50000)
        ->and($data['permintaan_dana']['menunggu'])->toBe(1);
});

test('per_page is capped at 100', function (): void {
    apiScopeFixture();
    $admin = User::factory()->admin()->create();

    $meta = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/opds?per_page=5000')
        ->json('meta');

    expect($meta['per_page'])->toBe(100);
});

test('unauthenticated access to protected resources is rejected', function (): void {
    apiScopeFixture();

    foreach (['/api/v1/opds', '/api/v1/belanja', '/api/v1/dashboard', '/api/v1/reports/penerimaan'] as $url) {
        $this->getJson($url)->assertStatus(401);
    }
});
