<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\RekeningBank;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

uses()->group('api');

function apiPenerimaanFixture(): array
{
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $admin = User::factory()->admin()->create();

    $rekening = Rekening::create(['kode' => '4.1.2', 'nama' => 'Pendapatan', 'tipe' => 'pendapatan']);
    $kasRekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Kas Daerah', 'tipe' => 'kas']);

    $bank = RekeningBank::create([
        'bank_name' => 'Bank BRI', 'account_number' => '001-01-000111-7',
        'account_name' => 'Dinas A', 'is_active' => true,
    ]);
    $inactiveBank = RekeningBank::create([
        'bank_name' => 'Bank BNI', 'account_number' => '009-01-000222-8',
        'account_name' => 'Dinas A', 'is_active' => false,
    ]);

    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
        'target' => 1000000,
    ]);

    return compact('opd', 'opdB', 'user', 'admin', 'rekening', 'kasRekening', 'bank', 'inactiveBank', 'penerimaan');
}

test('penerimaan realization is computed from transactions, never persisted', function (): void {
    ['user' => $user, 'penerimaan' => $penerimaan] = apiPenerimaanFixture();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaan->id,
            'realisasi' => 300000,
            'tanggal' => now()->toDateString(),
            'bkus' => [
                ['nomor_bku' => 'BKU-1', 'tanggal_bku' => now()->toDateString(), 'nilai' => 300000, 'rekening_bank_id' => null],
            ],
        ])
        ->assertStatus(201)
        ->assertJson(fn (AssertableJson $json) => $json->where('success', true)->etc());

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaan->id,
            'realisasi' => 200000,
            'tanggal' => now()->toDateString(),
        ])->assertStatus(201);

    $resource = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/penerimaan/{$penerimaan->id}")
        ->json('data');

    // Computed: 300k + 200k = 500k; no realisasi column was written.
    expect((float) $resource['realisasi'])->toBe(500000.0)
        ->and((float) $resource['persentase'])->toBe(50.0)
        ->and($penerimaan->fresh()->getAttributes())->not->toHaveKey('realisasi');
});

test('BKU total must equal transaction realisasi when BKU rows exist', function (): void {
    ['user' => $user, 'penerimaan' => $penerimaan] = apiPenerimaanFixture();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaan->id,
            'realisasi' => 400000,
            'tanggal' => now()->toDateString(),
            'bkus' => [
                ['nomor_bku' => 'BKU-1', 'tanggal_bku' => now()->toDateString(), 'nilai' => 250000],
            ],
        ])
        ->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('success', false)
            ->has('errors.bkus')
            ->etc());

    expect(TransaksiPenerimaan::count())->toBe(0);
});

test('inactive bank account cannot be used on BKU rows', function (): void {
    ['user' => $user, 'penerimaan' => $penerimaan, 'inactiveBank' => $inactiveBank] = apiPenerimaanFixture();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaan->id,
            'realisasi' => 100000,
            'tanggal' => now()->toDateString(),
            'bkus' => [
                ['nomor_bku' => 'BKU-1', 'tanggal_bku' => now()->toDateString(), 'nilai' => 100000, 'rekening_bank_id' => $inactiveBank->id],
            ],
        ])
        ->assertStatus(422);

    // Laravel returns flat dotted error keys (bkus.0.rekening_bank_id).
    expect($response->json('errors'))->toHaveKey('bkus.0.rekening_bank_id');
});

test('nomor_registrasi is generated automatically and cannot be supplied', function (): void {
    ['user' => $user, 'penerimaan' => $penerimaan] = apiPenerimaanFixture();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaan->id,
            'nomor_registrasi' => 'HACKED-001',
            'realisasi' => 100000,
            'tanggal' => now()->toDateString(),
        ])
        ->assertStatus(201);

    $transaksi = TransaksiPenerimaan::findOrFail($response->json('data.id'));

    expect($transaksi->nomor_registrasi)->toStartWith('REG-')
        ->and($transaksi->nomor_registrasi)->not->toBe('HACKED-001')
        ->and($transaksi->nomor_registrasi)->toMatch('/^REG-\\d{5}\\/'.now()->year.'$/');
});

test('nomor_registrasi is immutable on update', function (): void {
    ['user' => $user, 'penerimaan' => $penerimaan] = apiPenerimaanFixture();

    $transaksi = TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'nomor_registrasi' => 'REG-00001/'.now()->year,
        'realisasi' => 100000,
        'tanggal' => now(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/transaksi-penerimaan/{$transaksi->id}", [
            'penerimaan_id' => $penerimaan->id,
            'nomor_registrasi' => 'REG-99999/'.now()->year,
            'realisasi' => 150000,
            'tanggal' => now()->toDateString(),
        ])
        ->assertOk();

    expect($transaksi->fresh()->nomor_registrasi)->toBe('REG-00001/'.now()->year)
        ->and((float) $transaksi->fresh()->realisasi)->toBe(150000.0);
});

test('nested BKU endpoint enforces sum rule on create and update', function (): void {
    ['user' => $user, 'penerimaan' => $penerimaan] = apiPenerimaanFixture();

    $transaksi = TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'nomor_registrasi' => 'REG-00001/'.now()->year,
        'realisasi' => 300000,
        'tanggal' => now(),
    ]);

    // Adding a BKU that would make the total exceed realisasi is rejected.
    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/transaksi-penerimaan/{$transaksi->id}/bkus", [
            'nomor_bku' => 'BKU-2',
            'tanggal_bku' => now()->toDateString(),
            'nilai' => 999999,
        ])
        ->assertStatus(422);

    // Correct BKU row is accepted.
    $bku = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/transaksi-penerimaan/{$transaksi->id}/bkus", [
            'nomor_bku' => 'BKU-2',
            'tanggal_bku' => now()->toDateString(),
            'nilai' => 300000,
        ])
        ->assertStatus(201)
        ->json('data');

    // Updating it to an inconsistent value is rejected.
    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/v1/transaksi-penerimaan/{$transaksi->id}/bkus/{$bku['id']}", [
            'nomor_bku' => 'BKU-2',
            'tanggal_bku' => now()->toDateString(),
            'nilai' => 100000,
        ])
        ->assertStatus(422);

    expect($transaksi->bkus()->count())->toBe(1)
        ->and((float) $transaksi->bkus()->sum('nilai'))->toBe(300000.0);
});

test('opd user cannot create transaksi against another opd penerimaan', function (): void {
    ['user' => $user, 'opdB' => $opdB] = apiPenerimaanFixture();

    $penerimaanB = Penerimaan::create([
        'opd_id' => $opdB->id,
        'target' => 500000,
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaanB->id,
            'realisasi' => 100000,
            'tanggal' => now()->toDateString(),
        ])
        ->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json->has('errors.penerimaan_id')->etc());

    expect(TransaksiPenerimaan::count())->toBe(0);
});

test('opd user cannot access another opd penerimaan', function (): void {
    ['user' => $user, 'opdB' => $opdB] = apiPenerimaanFixture();

    $penerimaanB = Penerimaan::create([
        'opd_id' => $opdB->id,
        'target' => 500000,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/penerimaan/{$penerimaanB->id}")
        ->assertForbidden();
});

test('penerimaan with transactions cannot be deleted', function (): void {
    ['user' => $user, 'penerimaan' => $penerimaan] = apiPenerimaanFixture();

    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'nomor_registrasi' => 'REG-00001/'.now()->year,
        'realisasi' => 100000,
        'tanggal' => now(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/penerimaan/{$penerimaan->id}")
        ->assertStatus(422);

    expect(Penerimaan::find($penerimaan->id))->not->toBeNull();
});

test('transaksi penerimaan pagination meta is returned', function (): void {
    ['user' => $user, 'penerimaan' => $penerimaan] = apiPenerimaanFixture();

    foreach (range(1, 5) as $i) {
        TransaksiPenerimaan::create([
            'penerimaan_id' => $penerimaan->id,
            'nomor_registrasi' => sprintf('REG-%05d/%s', $i, now()->year),
            'realisasi' => 10000 * $i,
            'tanggal' => now(),
        ]);
    }

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/transaksi-penerimaan?page=1&per_page=2')
        ->assertOk();

    $meta = $response->json('meta');

    expect($meta['current_page'])->toBe(1)
        ->and($meta['per_page'])->toBe(2)
        ->and($meta['total'])->toBe(5)
        ->and($meta['last_page'])->toBe(3)
        ->and(count($response->json('data')))->toBe(2);
});

test('date range filters apply to transaksi penerimaan', function (): void {
    ['user' => $user, 'penerimaan' => $penerimaan] = apiPenerimaanFixture();

    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'nomor_registrasi' => 'REG-00001/'.now()->year,
        'realisasi' => 10000,
        'tanggal' => '2026-01-15',
    ]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'nomor_registrasi' => 'REG-00002/'.now()->year,
        'realisasi' => 20000,
        'tanggal' => '2026-06-01',
    ]);

    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/transaksi-penerimaan?tanggal_dari=2026-03-01&tanggal_sampai=2026-12-31')
        ->json('data');

    expect(count($data))->toBe(1)
        ->and($data[0]['nomor_registrasi'])->toBe('REG-00002/'.now()->year);
});
