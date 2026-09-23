<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use App\Models\RekeningBank;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\TransaksiPenerimaanBku;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

function makeSumberDana(string $name): SumberDana
{
    return SumberDana::create(['nama_sumber_dana' => $name]);
}

test('admin can create a penerimaan with detail sumber dana rows', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = makeSumberDana('DAU');

    $this->actingAs($admin)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'target' => 1000000,
            'details' => [
                ['sumber_dana_id' => $sumberDana->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('penerimaan_details', [
        'sumber_dana_id' => $sumberDana->id,
    ]);
});

test('admin can create a penerimaan with multiple detail sumber dana rows', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumber1 = makeSumberDana('DAU');
    $sumber2 = makeSumberDana('DAK');

    $this->actingAs($admin)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'target' => 2000000,
            'details' => [
                ['sumber_dana_id' => $sumber1->id],
                ['sumber_dana_id' => $sumber2->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    $master = Penerimaan::where('opd_id', $opd->id)->first();
    expect($master->details()->count())->toBe(2);
});

test('cannot create a detail with a nonexistent sumber dana', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $this->actingAs($admin)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'target' => 1000000,
            'details' => [
                ['sumber_dana_id' => 99999],
            ],
        ])
        ->assertSessionHasErrors('details.0.sumber_dana_id');

    $this->assertDatabaseCount('penerimaan_details', 0);
});

test('blank detail rows are ignored', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $this->actingAs($admin)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'target' => 1000000,
            'details' => [
                ['sumber_dana_id' => ''],
            ],
        ])
        ->assertSessionHasNoErrors();

    $master = Penerimaan::where('opd_id', $opd->id)->first();
    expect($master)->not->toBeNull()
        ->and($master->details()->count())->toBe(0);
});

test('opd user can manage details of their own opd only', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);
    $sumberDana = makeSumberDana('DAU');

    $this->actingAs($userA)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opdB->id,
            'target' => 1000000,
            'details' => [
                ['sumber_dana_id' => $sumberDana->id],
            ],
        ])
        ->assertSessionHasErrors('opd_id');

    $this->actingAs($userA)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opdA->id,
            'target' => 1000000,
            'details' => [
                ['sumber_dana_id' => $sumberDana->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    $master = Penerimaan::where('opd_id', $opdA->id)->first();
    expect($master)->not->toBeNull()
        ->and($master->details()->count())->toBe(1);
});

test('duplicate sumber dana combinations are rejected within one penerimaan', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = makeSumberDana('DAU');

    $this->actingAs($admin)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'target' => 1000000,
            'details' => [
                ['sumber_dana_id' => $sumberDana->id],
                ['sumber_dana_id' => $sumberDana->id],
            ],
        ])
        ->assertSessionHasErrors('details.1.sumber_dana_id');

    $this->assertDatabaseCount('penerimaan_details', 0);
});

test('admin can update details to add, edit and remove rows', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumber1 = makeSumberDana('DAU');
    $sumber2 = makeSumberDana('DAK');

    $master = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);
    $detail = PenerimaanDetail::create([
        'penerimaan_id' => $master->id,
        'sumber_dana_id' => $sumber1->id,
    ]);

    $this->actingAs($admin)
        ->from("/master-data/penerimaan/{$master->id}/edit")
        ->put("/master-data/penerimaan/{$master->id}", [
            'opd_id' => $opd->id,
            'target' => 1500000,
            'details' => [
                ['id' => $detail->id, 'sumber_dana_id' => $sumber2->id],
                ['sumber_dana_id' => $sumber1->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($detail->fresh()->sumber_dana_id)->toBe($sumber2->id)
        ->and($master->details()->count())->toBe(2);

    $this->actingAs($admin)
        ->put("/master-data/penerimaan/{$master->id}", [
            'opd_id' => $opd->id,
            'target' => 1500000,
            'details' => [
                ['id' => $detail->id, 'sumber_dana_id' => $sumber2->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($master->details()->count())->toBe(1)
        ->and($master->details()->where('sumber_dana_id', $sumber1->id)->exists())->toBeFalse();
});

test('detail not owned by the penerimaan is rejected on update', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = makeSumberDana('DAU');

    $masterA = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);
    $masterB = Penerimaan::create(['opd_id' => $opd->id, 'target' => 2000000]);
    $detailOfB = PenerimaanDetail::create([
        'penerimaan_id' => $masterB->id,
        'sumber_dana_id' => $sumberDana->id,
    ]);

    $this->actingAs($admin)
        ->from("/master-data/penerimaan/{$masterA->id}/edit")
        ->put("/master-data/penerimaan/{$masterA->id}", [
            'opd_id' => $opd->id,
            'target' => 1000000,
            'details' => [
                ['id' => $detailOfB->id, 'sumber_dana_id' => $sumberDana->id],
            ],
        ])
        ->assertSessionHasErrors('details.0.id');

    expect($masterA->details()->count())->toBe(0);
});

test('transaksi penerimaan dapat membooking rekening bank pada baris BKU', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $bank = RekeningBank::create([
        'bank_name' => 'Bank BPD',
        'account_number' => '0010-01-000123-7',
        'account_name' => 'Dinas A',
        'is_active' => true,
    ]);

    $sumberDana = makeSumberDana('DAU');

    $master = Penerimaan::create(['opd_id' => $opd->id, 'sumber_dana_id' => $sumberDana->id, 'target' => 1000000]);

    $this->actingAs($admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $master->id,
            'realisasi' => 400000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 400000, 'rekening_bank_id' => $bank->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('transaksi_penerimaan_bkus', [
        'nomor_bku' => 'BKU-001',
        'rekening_bank_id' => $bank->id,
    ]);

    // The bank lives on the BKU row, not on the transaction header.
    expect(Schema::hasColumn('transaksi_penerimaans', 'rekening_bank_id'))->toBeFalse()
        ->and(Schema::hasColumn('transaksi_penerimaan_bkus', 'rekening_id'))->toBeFalse()
        ->and((float) $master->fresh()->realisasi)->toBe(400000.0);
});

test('satu transaksi penerimaan dapat memakai rekening bank berbeda di tiap baris BKU', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $bankA = RekeningBank::create(['bank_name' => 'Bank BPD', 'account_number' => '0010-01-000123-7', 'account_name' => 'A', 'is_active' => true]);
    $bankB = RekeningBank::create(['bank_name' => 'Bank BRI', 'account_number' => '1111-01-000999-9', 'account_name' => 'B', 'is_active' => true]);

    $sumberDana = makeSumberDana('DAU');

    $master = Penerimaan::create(['opd_id' => $opd->id, 'sumber_dana_id' => $sumberDana->id, 'target' => 1000000]);

    $this->actingAs($admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $master->id,
            'realisasi' => 400000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 250000, 'rekening_bank_id' => $bankA->id],
                ['nomor_bku' => 'BKU-002', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 150000, 'rekening_bank_id' => $bankB->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    $transaksi = TransaksiPenerimaan::where('penerimaan_id', $master->id)->first();

    expect($transaksi->bkus()->orderBy('nomor_bku')->pluck('rekening_bank_id')->all())
        ->toBe([$bankA->id, $bankB->id]);
});

test('transaksi penerimaan menolak rekening bank yang tidak aktif pada baris BKU', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $inactiveBank = RekeningBank::create([
        'bank_name' => 'Bank BNI',
        'account_number' => '0091-01-000789-9',
        'account_name' => 'Dinas A',
        'is_active' => false,
    ]);

    $sumberDana = makeSumberDana('DAU');

    $master = Penerimaan::create(['opd_id' => $opd->id, 'sumber_dana_id' => $sumberDana->id, 'target' => 1000000]);

    $this->actingAs($admin)
        ->from('/transaksi-penerimaan/create')
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $master->id,
            'realisasi' => 400000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 400000, 'rekening_bank_id' => $inactiveBank->id],
            ],
        ])
        ->assertSessionHasErrors('bkus.0.rekening_bank_id');

    expect(session('errors')->first('bkus.0.rekening_bank_id'))->toBe('Rekening bank tidak aktif dan tidak dapat digunakan.')
        ->and(TransaksiPenerimaan::count())->toBe(0);
});

test('baris BKU dapat disimpan tanpa rekening bank', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $sumberDana = makeSumberDana('DAU');

    $master = Penerimaan::create(['opd_id' => $opd->id, 'sumber_dana_id' => $sumberDana->id, 'target' => 1000000]);

    $this->actingAs($admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $master->id,
            'realisasi' => 400000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 400000, 'rekening_bank_id' => ''],
            ],
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('transaksi_penerimaan_bkus', [
        'nomor_bku' => 'BKU-001',
        'rekening_bank_id' => null,
    ]);
});

test('baris BKU dapat berpindah rekening bank saat transaksi diupdate', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $bankA = RekeningBank::create(['bank_name' => 'Bank BPD', 'account_number' => '0010-01-000123-7', 'account_name' => 'A', 'is_active' => true]);
    $bankB = RekeningBank::create(['bank_name' => 'Bank BRI', 'account_number' => '1111-01-000999-9', 'account_name' => 'B', 'is_active' => true]);

    $sumberDana = makeSumberDana('DAU');

    $master = Penerimaan::create(['opd_id' => $opd->id, 'sumber_dana_id' => $sumberDana->id, 'target' => 1000000]);
    $transaksi = TransaksiPenerimaan::create([
        'penerimaan_id' => $master->id,
        'realisasi' => 250000,
        'tanggal' => now(),
    ]);
    $bku = TransaksiPenerimaanBku::create([
        'transaksi_penerimaan_id' => $transaksi->id,
        'nomor_bku' => 'BKU-001',
        'tanggal_bku' => now(),
        'nilai' => 250000,
        'rekening_bank_id' => $bankA->id,
    ]);

    $this->actingAs($admin)
        ->put("/transaksi-penerimaan/{$transaksi->id}", [
            'penerimaan_id' => $master->id,
            'realisasi' => 250000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['id' => $bku->id, 'nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 250000, 'rekening_bank_id' => $bankB->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($bku->fresh()->rekening_bank_id)->toBe($bankB->id)
        ->and((float) $master->fresh()->realisasi)->toBe(250000.0);
});
