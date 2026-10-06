<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\RekeningBank;
use App\Models\TransaksiPenerimaan;
use App\Models\TransaksiPenerimaanBku;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

test('admin can create a penerimaan keyed to a rekening and sub rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $utama = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan Pajak', 'tipe' => 'pendapatan']);
    $sub = Rekening::create(['kode' => '4.1.1.01', 'nama' => 'Pajak Daerah', 'tipe' => 'pendapatan', 'parent_id' => $utama->id]);

    $this->actingAs($admin)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $utama->id,
            'sub_rekening_id' => $sub->id,
            'target' => 1000000,
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('penerimaans', [
        'opd_id' => $opd->id,
        'rekening_id' => $utama->id,
        'sub_rekening_id' => $sub->id,
        'target' => 1000000,
    ]);
});

test('penerimaan sub rekening must be a detail of the chosen rekening utama', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $utama = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan Pajak', 'tipe' => 'pendapatan']);
    $other = Rekening::create(['kode' => '4.1.2', 'nama' => 'Pendapatan Retribusi', 'tipe' => 'pendapatan']);
    $orphan = Rekening::create(['kode' => '4.1.2.01', 'nama' => 'Retribusi Daerah', 'tipe' => 'pendapatan', 'parent_id' => $other->id]);

    $this->actingAs($admin)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $utama->id,
            'sub_rekening_id' => $orphan->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('sub_rekening_id');

    $this->assertDatabaseCount('penerimaans', 0);
});

test('penerimaan sub rekening must share the pendapatan tipe', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $utama = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan Pajak', 'tipe' => 'pendapatan']);
    $kasSub = Rekening::create(['kode' => '1.1.1.01', 'nama' => 'Kas Tunai', 'tipe' => 'kas', 'parent_id' => $utama->id]);

    $this->actingAs($admin)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $utama->id,
            'sub_rekening_id' => $kasSub->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('sub_rekening_id');

    $this->assertDatabaseCount('penerimaans', 0);
});

test('penerimaan sub rekening requires a rekening utama first', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sub = Rekening::create(['kode' => '4.1.1.01', 'nama' => 'Pajak Daerah', 'tipe' => 'pendapatan']);

    $this->actingAs($admin)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'sub_rekening_id' => $sub->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('sub_rekening_id');

    $this->assertDatabaseCount('penerimaans', 0);
});

test('penerimaan rekening utama must be a pendapatan rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $kas = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Umum', 'tipe' => 'kas']);

    $this->actingAs($admin)
        ->from('/master-data/penerimaan/create')
        ->post('/master-data/penerimaan', [
            'opd_id' => $opd->id,
            'rekening_id' => $kas->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('rekening_id');

    $this->assertDatabaseCount('penerimaans', 0);
});

test('opd user can manage penerimaan of their own opd only', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opdB->id,
            'target' => 1000000,
        ])
        ->assertSessionHasErrors('opd_id');

    $this->actingAs($userA)
        ->post('/master-data/penerimaan', [
            'opd_id' => $opdA->id,
            'target' => 1000000,
        ])
        ->assertSessionHasNoErrors();

    expect(Penerimaan::where('opd_id', $opdA->id)->count())->toBe(1)
        ->and(Penerimaan::where('opd_id', $opdB->id)->count())->toBe(0);
});

test('admin can update a penerimaan to retarget its sub rekening', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $utama = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan Pajak', 'tipe' => 'pendapatan']);
    $subA = Rekening::create(['kode' => '4.1.1.01', 'nama' => 'Pajak A', 'tipe' => 'pendapatan', 'parent_id' => $utama->id]);
    $subB = Rekening::create(['kode' => '4.1.1.02', 'nama' => 'Pajak B', 'tipe' => 'pendapatan', 'parent_id' => $utama->id]);

    $master = Penerimaan::create([
        'opd_id' => $opd->id,
        'rekening_id' => $utama->id,
        'sub_rekening_id' => $subA->id,
        'target' => 1000000,
    ]);

    $this->actingAs($admin)
        ->from("/master-data/penerimaan/{$master->id}/edit")
        ->put("/master-data/penerimaan/{$master->id}", [
            'opd_id' => $opd->id,
            'rekening_id' => $utama->id,
            'sub_rekening_id' => $subB->id,
            'target' => 1500000,
        ])
        ->assertSessionHasNoErrors();

    expect($master->fresh()->sub_rekening_id)->toBe($subB->id);
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

    $master = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);

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
        ->and((float) $master->fresh()->realisasi)->toBe(400000.0);
});

test('baris BKU dapat membooking opd dan rekening akuntansi per baris', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $utama = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan Pajak', 'tipe' => 'pendapatan']);
    $sub = Rekening::create(['kode' => '4.1.1.01', 'nama' => 'Pajak Daerah', 'tipe' => 'pendapatan', 'parent_id' => $utama->id]);

    $master = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);

    $this->actingAs($admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $master->id,
            'realisasi' => 400000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                [
                    'opd_id' => $opd->id,
                    'nomor_bku' => 'BKU-001',
                    'tanggal_bku' => now()->format('Y-m-d'),
                    'nilai' => 400000,
                    'rekening_id' => $utama->id,
                    'sub_rekening_id' => $sub->id,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('transaksi_penerimaan_bkus', [
        'nomor_bku' => 'BKU-001',
        'opd_id' => $opd->id,
        'rekening_id' => $utama->id,
        'sub_rekening_id' => $sub->id,
    ]);
});

test('baris BKU menolak rekening utama yang bukan pendapatan', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $kas = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Umum', 'tipe' => 'kas']);

    $master = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);

    $this->actingAs($admin)
        ->from('/transaksi-penerimaan/create')
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $master->id,
            'realisasi' => 400000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 400000, 'rekening_id' => $kas->id],
            ],
        ])
        ->assertSessionHasErrors('bkus.0.rekening_id');

    expect(TransaksiPenerimaan::count())->toBe(0);
});

test('baris BKU menolak sub rekening yang bukan detail dari rekening utamanya', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $utama = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan Pajak', 'tipe' => 'pendapatan']);
    $other = Rekening::create(['kode' => '4.1.2', 'nama' => 'Pendapatan Retribusi', 'tipe' => 'pendapatan']);
    $orphan = Rekening::create(['kode' => '4.1.2.01', 'nama' => 'Retribusi Daerah', 'tipe' => 'pendapatan', 'parent_id' => $other->id]);

    $master = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);

    $this->actingAs($admin)
        ->from('/transaksi-penerimaan/create')
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $master->id,
            'realisasi' => 400000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                [
                    'nomor_bku' => 'BKU-001',
                    'tanggal_bku' => now()->format('Y-m-d'),
                    'nilai' => 400000,
                    'rekening_id' => $utama->id,
                    'sub_rekening_id' => $orphan->id,
                ],
            ],
        ])
        ->assertSessionHasErrors('bkus.0.sub_rekening_id');

    expect(TransaksiPenerimaan::count())->toBe(0);
});

test('satu transaksi penerimaan dapat memakai rekening bank berbeda di tiap baris BKU', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $bankA = RekeningBank::create(['bank_name' => 'Bank BPD', 'account_number' => '0010-01-000123-7', 'account_name' => 'A', 'is_active' => true]);
    $bankB = RekeningBank::create(['bank_name' => 'Bank BRI', 'account_number' => '1111-01-000999-9', 'account_name' => 'B', 'is_active' => true]);

    $master = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);

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

    $master = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);

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

    $master = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);

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

    $master = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);
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
