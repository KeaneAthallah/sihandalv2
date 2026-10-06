<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\RekeningBank;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\TransaksiPenerimaan;
use App\Models\TransaksiPenerimaanBku;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

uses()->group('api');

test('rekening bank crud works with unique account numbers', function (): void {
    $admin = User::factory()->admin()->create();

    $bank = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/rekening-banks', [
            'bank_name' => 'Bank BRI',
            'account_number' => '001-999',
            'account_name' => 'Dinas A',
        ])
        ->assertStatus(201)
        ->json('data');

    // Store rules do not set is_active; model default applies (true).
    expect($bank['is_active'] ?? RekeningBank::find($bank['id'])->is_active)->toBeTrue();

    // Duplicate account number is rejected.
    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/rekening-banks', [
            'bank_name' => 'Bank BRI',
            'account_number' => '001-999',
            'account_name' => 'Duplicate',
        ])
        ->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json->has('errors.account_number')->etc());

    // Update works.
    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/rekening-banks/{$bank['id']}", [
            'bank_name' => 'Bank BRI',
            'account_number' => '001-999',
            'account_name' => 'Dinas A',
            'is_active' => false,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('data.is_active', false)->etc());
});

test('rekening bank used in transactions cannot be deleted, only deactivated', function (): void {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $admin = User::factory()->admin()->create();

    $bank = RekeningBank::create([
        'bank_name' => 'Bank BRI', 'account_number' => '001-777',
        'account_name' => 'Dinas A', 'is_active' => true,
    ]);

    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'target' => 100000,
    ]);

    $transaksi = TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'nomor_registrasi' => 'REG-00001/'.now()->year,
        'realisasi' => 100000,
        'tanggal' => now(),
    ]);

    TransaksiPenerimaanBku::create([
        'transaksi_penerimaan_id' => $transaksi->id,
        'nomor_bku' => 'BKU-1',
        'tanggal_bku' => now(),
        'nilai' => 100000,
        'rekening_bank_id' => $bank->id,
    ]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/rekening-banks/{$bank->id}")
        ->assertStatus(422);

    expect(RekeningBank::find($bank->id))->not->toBeNull();
});

test('rekening kas rejects non-kas parents and enforces hierarchy', function (): void {
    $admin = User::factory()->admin()->create();

    // Non-kas parent hosting a kas detail is invalid.
    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/rekenings', [
            'kode' => '1.1.99',
            'nama' => 'Detail Salah',
            'tipe' => 'kas',
            'parent_id' => 0,
        ])
        ->assertStatus(422);

    $kasInduk = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Induk', 'tipe' => 'kas']);

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/rekenings', [
            'kode' => '1.1.1.01',
            'nama' => 'Kas Detail',
            'tipe' => 'kas',
            'parent_id' => $kasInduk->id,
        ])
        ->assertStatus(201);

    // Parent with children cannot be deleted.
    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/rekenings/{$kasInduk->id}")
        ->assertStatus(422);

    // Self-parenting is rejected.
    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/rekenings/{$kasInduk->id}", [
            'kode' => '1.1.1',
            'nama' => 'Kas Induk',
            'tipe' => 'kas',
            'parent_id' => $kasInduk->id,
        ])
        ->assertStatus(422);
});

test('rekening kas writes are admin only', function (): void {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/rekenings', [
            'kode' => '1.1.5', 'nama' => 'Kas Baru', 'tipe' => 'kas',
        ])
        ->assertForbidden();
});

test('posisi kas requires a named rekening and saldo', function (): void {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $admin = User::factory()->admin()->create();

    // Posisi kas is standalone: it no longer references a kas rekening,
    // so nama_rekening and saldo are what identify the row.
    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/posisi-kas', [
            'opd_id' => $opd->id,
        ])
        ->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('errors.nama_rekening')
            ->has('errors.saldo')
            ->etc());
});

test('posisi kas stores the named rekening and saldo', function (): void {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $admin = User::factory()->admin()->create();

    $data = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/posisi-kas', [
            'opd_id' => $opd->id,
            'nama_rekening' => 'Kas Umum Daerah',
            'nomor_rekening' => '001.01.000123.7',
            'saldo' => 1300000,
        ])
        ->assertStatus(201)
        ->json('data');

    expect($data['nama_rekening'])->toBe('Kas Umum Daerah')
        ->and($data['nomor_rekening'])->toBe('001.01.000123.7')
        ->and((float) $data['saldo'])->toBe(1300000.0);
});

test('fiscal year activate ensures single active year', function (): void {
    $admin = User::factory()->admin()->create();

    $ta1 = TahunAnggaran::create(['tahun' => '2025', 'tanggal_mulai' => '2025-01-01', 'tanggal_selesai' => '2025-12-31', 'status' => 'open', 'is_active' => false]);
    $ta2 = TahunAnggaran::create(['tahun' => '2026', 'tanggal_mulai' => '2026-01-01', 'tanggal_selesai' => '2026-12-31', 'status' => 'open', 'is_active' => false]);

    $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/tahun-anggaran/{$ta1->id}/activate")
        ->assertOk();

    expect($ta1->fresh()->is_active)->toBeTrue();

    $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/tahun-anggaran/{$ta2->id}/activate")
        ->assertOk();

    expect($ta1->fresh()->is_active)->toBeFalse()
        ->and($ta2->fresh()->is_active)->toBeTrue()
        ->and(TahunAnggaran::where('is_active', true)->count())->toBe(1);

    // Active-year endpoint works.
    $active = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/tahun-anggaran-active')
        ->assertOk()
        ->json('data');

    expect($active['id'])->toBe($ta2->id);
});

test('fiscal year writes are admin only', function (): void {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tahun-anggaran', [
            'tahun' => '2027',
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-12-31',
        ])
        ->assertForbidden();
});

test('audit log continues to record api writes', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/sumber-dana', ['nama_sumber_dana' => 'BLUD API'])
        ->assertStatus(201);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'created',
        'auditable_type' => 'App\\Models\\SumberDana',
    ]);

    $logs = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/audit-logs?action=created')
        ->assertOk()
        ->json('data');

    expect(count($logs))->toBeGreaterThanOrEqual(1);
});

test('sumber dana crud works via api', function (): void {
    $admin = User::factory()->admin()->create();

    $sumber = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/sumber-dana', ['nama_sumber_dana' => 'DID'])
        ->assertStatus(201)
        ->json('data');

    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/sumber-dana/{$sumber['id']}", ['nama_sumber_dana' => 'DAK Fisik'])
        ->assertOk();

    expect(SumberDana::find($sumber['id'])->nama_sumber_dana)->toBe('DAK Fisik');

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/sumber-dana/{$sumber['id']}")
        ->assertOk();

    expect(SumberDana::find($sumber['id']))->toBeNull();
});
