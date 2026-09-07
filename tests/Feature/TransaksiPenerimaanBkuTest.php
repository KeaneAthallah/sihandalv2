<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\TransaksiPenerimaanBku;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $this->opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $this->user = User::factory()->create(['role' => 'opd', 'opd_id' => $this->opd->id]);
    $this->rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);
    $this->sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $this->penerimaan = Penerimaan::create([
        'opd_id' => $this->opd->id,
        'rekening_id' => $this->rekening->id,
        'sumber_dana_id' => $this->sumberDana->id,
        'nama_sumber_dana' => 'DAU',
        'target' => 100000000,
    ]);
});

test('admin can create transaksi with 1 bku', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-001',
            'realisasi' => 500000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 500000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect(TransaksiPenerimaan::count())->toBe(1)
        ->and(TransaksiPenerimaanBku::count())->toBe(1);

    $tx = TransaksiPenerimaan::first();
    expect($tx->nomor_registrasi)->toBe('REG-00001/'.now()->year)
        ->and((float) $tx->realisasi)->toBe(500000.0);

    $bku = $tx->bkus()->first();
    expect($bku->nomor_bku)->toBe('BKU-001')
        ->and((float) $bku->nilai)->toBe(500000.0)
        ->and($bku->rekening_id)->toBe($this->rekening->id);
});

test('admin can create transaksi with 2 bku', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-002',
            'realisasi' => 1000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 400000, 'rekening_id' => $this->rekening->id],
                ['nomor_bku' => 'BKU-002', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 600000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    $tx = TransaksiPenerimaan::first();
    expect($tx->bkus()->count())->toBe(2);
    expect($tx->totalBku())->toBe(1000000.0);
});

test('admin can create transaksi with 5 bku', function () {
    $values = [100000, 200000, 150000, 300000, 250000];
    $bkus = collect($values)->map(fn ($v, $i) => [
        'nomor_bku' => 'BKU-'.($i + 1),
        'tanggal_bku' => now()->format('Y-m-d'),
        'nilai' => $v,
        'rekening_id' => $this->rekening->id,
    ])->toArray();

    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-005',
            'realisasi' => 1000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => $bkus,
        ])
        ->assertSessionHasNoErrors();

    $tx = TransaksiPenerimaan::first();
    expect($tx->bkus()->count())->toBe(5)
        ->and($tx->totalBku())->toBe(1000000.0);
});

test('transaksi fails when total bku does not equal realisasi', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-BAD',
            'realisasi' => 1000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 400000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasErrors('bkus');

    expect(TransaksiPenerimaan::count())->toBe(0)
        ->and(TransaksiPenerimaanBku::count())->toBe(0);
});

test('transaksi without bku is valid', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-NOBKU',
            'realisasi' => 10000000,
            'tanggal' => now()->format('Y-m-d'),
            'keterangan' => 'Penerimaan dicatat, BKU menyusul',
        ])
        ->assertSessionHasNoErrors();

    expect(TransaksiPenerimaan::count())->toBe(1)
        ->and(TransaksiPenerimaanBku::count())->toBe(0);

    $tx = TransaksiPenerimaan::first();
    expect($tx->nomor_registrasi)->toBe('REG-00001/'.now()->year)
        ->and((float) $tx->realisasi)->toBe(10000000.0)
        ->and($tx->bkus()->count())->toBe(0);
});

test('transaksi with empty bku array is valid', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-EMPTY',
            'realisasi' => 10000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [],
        ])
        ->assertSessionHasNoErrors();

    expect(TransaksiPenerimaan::count())->toBe(1)
        ->and(TransaksiPenerimaanBku::count())->toBe(0);
});

test('nomor registrasi is auto-generated when not submitted', function () {
    $this->actingAs($this->admin)
        ->from('/transaksi-penerimaan/create')
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'realisasi' => 10000000,
            'tanggal' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasNoErrors();

    $tx = TransaksiPenerimaan::first();
    expect($tx)->not->toBeNull()
        ->and($tx->nomor_registrasi)->toBe('REG-00001/'.now()->year);
});

test('submitted nomor registrasi is ignored and rebuilt by the system', function () {
    $this->actingAs($this->admin)
        ->from('/transaksi-penerimaan/create')
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-MANUAL',
            'realisasi' => 10000000,
            'tanggal' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasNoErrors();

    $tx = TransaksiPenerimaan::first();
    expect($tx->nomor_registrasi)->toBe('REG-00001/'.now()->year)
        ->and($tx->nomor_registrasi)->not->toBe('REG-MANUAL');
});

test('transaksi fails when bku has missing required fields', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-INCOMPLETE',
            'realisasi' => 1000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => '', 'tanggal_bku' => '', 'nilai' => '', 'rekening_id' => ''],
            ],
        ])
        ->assertSessionHasErrors([
            'bkus.0.nomor_bku',
            'bkus.0.tanggal_bku',
            'bkus.0.nilai',
            'bkus.0.rekening_id',
        ]);

    expect(TransaksiPenerimaan::count())->toBe(0);
});

test('transaksi fails when bku rekening is not pendapatan type', function () {
    $rekeningBelanja = Rekening::create(['kode' => '5.1.1', 'nama' => 'Belanja', 'tipe' => 'belanja']);

    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-BAD-REK',
            'realisasi' => 1000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 1000000, 'rekening_id' => $rekeningBelanja->id],
            ],
        ])
        ->assertSessionHasErrors('bkus.0.rekening_id');

    expect(TransaksiPenerimaan::count())->toBe(0);
});

test('auto-generated nomor registrasi is sequential and unique', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'realisasi' => 500000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 500000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'realisasi' => 300000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-002', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 300000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    $numbers = TransaksiPenerimaan::orderBy('id')->pluck('nomor_registrasi')->all();

    expect($numbers)->toBe([
        'REG-00001/'.now()->year,
        'REG-00002/'.now()->year,
    ])->and(TransaksiPenerimaan::count())->toBe(2);
});

test('admin can update transaksi and add new bku', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-UPD',
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);
    $bku1 = $tx->bkus()->create([
        'nomor_bku' => 'BKU-001',
        'tanggal_bku' => now(),
        'nilai' => 500000,
        'rekening_id' => $this->rekening->id,
    ]);

    $this->actingAs($this->admin)
        ->put("/transaksi-penerimaan/{$tx->id}", [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-UPD',
            'realisasi' => 1000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['id' => $bku1->id, 'nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 600000, 'rekening_id' => $this->rekening->id],
                ['nomor_bku' => 'BKU-002', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 400000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    $tx->refresh();
    expect((float) $tx->realisasi)->toBe(1000000.0)
        ->and($tx->bkus()->count())->toBe(2);

    $bku1->refresh();
    expect((float) $bku1->nilai)->toBe(600000.0);
});

test('admin can update transaksi and delete bku', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-DEL',
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);
    $bku1 = $tx->bkus()->create(['nomor_bku' => 'BKU-001', 'tanggal_bku' => now(), 'nilai' => 300000, 'rekening_id' => $this->rekening->id]);
    $bku2 = $tx->bkus()->create(['nomor_bku' => 'BKU-002', 'tanggal_bku' => now(), 'nilai' => 200000, 'rekening_id' => $this->rekening->id]);

    $this->actingAs($this->admin)
        ->put("/transaksi-penerimaan/{$tx->id}", [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-DEL',
            'realisasi' => 300000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['id' => $bku1->id, 'nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 300000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($tx->fresh()->bkus()->count())->toBe(1)
        ->and(TransaksiPenerimaanBku::where('id', $bku2->id)->exists())->toBeFalse();
});

test('admin can update transaksi to remove all bku', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-REMOVE-ALL',
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-001', 'tanggal_bku' => now(), 'nilai' => 300000, 'rekening_id' => $this->rekening->id]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-002', 'tanggal_bku' => now(), 'nilai' => 200000, 'rekening_id' => $this->rekening->id]);

    // Remove all BKU: no bkus key submitted at all.
    $this->actingAs($this->admin)
        ->put("/transaksi-penerimaan/{$tx->id}", [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-REMOVE-ALL',
            'realisasi' => 500000,
            'tanggal' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasNoErrors();

    expect($tx->fresh()->bkus()->count())->toBe(0)
        ->and(TransaksiPenerimaanBku::count())->toBe(0);
});

test('admin can update transaksi to remove all bku with explicit empty array', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-REMOVE-EMPTY',
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-001', 'tanggal_bku' => now(), 'nilai' => 500000, 'rekening_id' => $this->rekening->id]);

    $this->actingAs($this->admin)
        ->put("/transaksi-penerimaan/{$tx->id}", [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-REMOVE-EMPTY',
            'realisasi' => 500000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [],
        ])
        ->assertSessionHasNoErrors();

    expect($tx->fresh()->bkus()->count())->toBe(0)
        ->and(TransaksiPenerimaanBku::count())->toBe(0);
});

test('admin can add bku later to a transaction that started without bku', function () {
    // Create first with no BKU.
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-LATER',
            'realisasi' => 10000000,
            'tanggal' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasNoErrors();

    $tx = TransaksiPenerimaan::first();
    expect($tx->bkus()->count())->toBe(0);

    // Then add BKU via update.
    $this->actingAs($this->admin)
        ->put("/transaksi-penerimaan/{$tx->id}", [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-LATER',
            'realisasi' => 10000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 4000000, 'rekening_id' => $this->rekening->id],
                ['nomor_bku' => 'BKU-002', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 6000000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    $tx->refresh();
    expect($tx->bkus()->count())->toBe(2)
        ->and($tx->totalBku())->toBe(10000000.0)
        ->and((float) $tx->realisasi)->toBe(10000000.0);
});

test('admin can update a bku-less transaction without submitting bkus', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-NOBKU-EDIT',
        'realisasi' => 10000000,
        'tanggal' => now(),
        'keterangan' => 'awal',
    ]);

    $this->actingAs($this->admin)
        ->put("/transaksi-penerimaan/{$tx->id}", [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-NOBKU-EDIT',
            'realisasi' => 12000000,
            'tanggal' => now()->format('Y-m-d'),
            'keterangan' => 'diubah',
        ])
        ->assertSessionHasNoErrors();

    $tx->refresh();
    expect((float) $tx->realisasi)->toBe(12000000.0)
        ->and($tx->keterangan)->toBe('diubah')
        ->and($tx->bkus()->count())->toBe(0);
});

test('deleting transaksi cascades to delete all bku', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-CAS',
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-001', 'tanggal_bku' => now(), 'nilai' => 250000, 'rekening_id' => $this->rekening->id]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-002', 'tanggal_bku' => now(), 'nilai' => 250000, 'rekening_id' => $this->rekening->id]);

    expect(TransaksiPenerimaanBku::count())->toBe(2);

    $this->actingAs($this->admin)
        ->delete("/transaksi-penerimaan/{$tx->id}");

    expect(TransaksiPenerimaan::count())->toBe(0)
        ->and(TransaksiPenerimaanBku::count())->toBe(0);
});

test('master penerimaan realisasi reflects sum of transactions', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-M1',
            'realisasi' => 300000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 300000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect((float) $this->penerimaan->fresh()->realisasi)->toBe(300000.0);

    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-M2',
            'realisasi' => 200000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-002', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 200000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect((float) $this->penerimaan->fresh()->realisasi)->toBe(500000.0);
});

test('bku does not cause double counting of realisasi', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-DC',
            'realisasi' => 1000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 400000, 'rekening_id' => $this->rekening->id],
                ['nomor_bku' => 'BKU-002', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 600000, 'rekening_id' => $this->rekening->id],
            ],
        ]);

    expect((float) $this->penerimaan->fresh()->realisasi)->toBe(1000000.0);
    expect((float) TransaksiPenerimaanBku::sum('nilai'))->toBe(1000000.0);
    expect((float) $this->penerimaan->fresh()->realisasi)->not->toBe(2000000.0);
});

test('opd user can create transaksi for own opd', function () {
    $this->actingAs($this->user)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-OPD',
            'realisasi' => 750000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 750000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect(TransaksiPenerimaan::count())->toBe(1);
});

test('opd user cannot create transaksi against another opd penerimaan', function () {
    $penerimaanB = Penerimaan::create([
        'opd_id' => $this->opdB->id,
        'nama_sumber_dana' => 'PAD B',
        'target' => 1000000,
    ]);

    $this->actingAs($this->user)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaanB->id,
            'nomor_registrasi' => 'REG-FORBID',
            'realisasi' => 100000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 100000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasErrors('penerimaan_id');

    expect(TransaksiPenerimaan::count())->toBe(0);
});

test('opd user cannot access other opd transaksi edit', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-OTHER',
        'realisasi' => 100000,
        'tanggal' => now(),
    ]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-001', 'tanggal_bku' => now(), 'nilai' => 100000, 'rekening_id' => $this->rekening->id]);

    $penerimaanB = Penerimaan::create([
        'opd_id' => $this->opdB->id,
        'nama_sumber_dana' => 'PAD B',
        'target' => 1000000,
    ]);
    $txB = TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaanB->id,
        'nomor_registrasi' => 'REG-OTHER2',
        'realisasi' => 200000,
        'tanggal' => now(),
    ]);
    $txB->bkus()->create(['nomor_bku' => 'BKU-002', 'tanggal_bku' => now(), 'nilai' => 200000, 'rekening_id' => $this->rekening->id]);

    $this->actingAs($this->user)
        ->get("/transaksi-penerimaan/{$txB->id}/edit")
        ->assertForbidden();
});

test('opd user cannot delete other opd transaksi', function () {
    $penerimaanB = Penerimaan::create([
        'opd_id' => $this->opdB->id,
        'nama_sumber_dana' => 'PAD B',
        'target' => 1000000,
    ]);
    $txB = TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaanB->id,
        'nomor_registrasi' => 'REG-DEL2',
        'realisasi' => 200000,
        'tanggal' => now(),
    ]);
    $txB->bkus()->create(['nomor_bku' => 'BKU-002', 'tanggal_bku' => now(), 'nilai' => 200000, 'rekening_id' => $this->rekening->id]);

    $this->actingAs($this->user)
        ->delete("/transaksi-penerimaan/{$txB->id}")
        ->assertForbidden();

    expect(TransaksiPenerimaan::count())->toBe(1);
});

test('admin can view all transaksi and their bku on index', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-VIEW',
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-001', 'tanggal_bku' => now(), 'nilai' => 300000, 'rekening_id' => $this->rekening->id]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-002', 'tanggal_bku' => now(), 'nilai' => 200000, 'rekening_id' => $this->rekening->id]);

    $this->actingAs($this->admin)
        ->get('/transaksi-penerimaan')
        ->assertSuccessful()
        ->assertSee('REG-VIEW')
        ->assertSee('BKU-001')
        ->assertSee('BKU-002');
});

test('index page shows correct jumlah bku count', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-CNT',
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-001', 'tanggal_bku' => now(), 'nilai' => 250000, 'rekening_id' => $this->rekening->id]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-002', 'tanggal_bku' => now(), 'nilai' => 250000, 'rekening_id' => $this->rekening->id]);

    $this->actingAs($this->admin)
        ->get('/transaksi-penerimaan')
        ->assertSuccessful()
        ->assertSeeInOrder(['Nomor Registrasi', 'REG-CNT']);
});

test('create page renders with rekening dropdown', function () {
    $this->actingAs($this->admin)
        ->get('/transaksi-penerimaan/create')
        ->assertSuccessful()
        ->assertSee('Nomor Registrasi')
        ->assertSee('Tambah BKU')
        ->assertSee('4.1.1');
});

test('edit page loads with existing bku data', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-EDIT',
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);
    $tx->bkus()->create(['nomor_bku' => 'BKU-001', 'tanggal_bku' => now(), 'nilai' => 500000, 'rekening_id' => $this->rekening->id]);

    $this->actingAs($this->admin)
        ->get("/transaksi-penerimaan/{$tx->id}/edit")
        ->assertSuccessful()
        ->assertSee('REG-EDIT')
        ->assertSee('BKU-001');
});

test('update fails when total bku does not equal new realisasi', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-UPD-BAD',
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);
    $bku1 = $tx->bkus()->create(['nomor_bku' => 'BKU-001', 'tanggal_bku' => now(), 'nilai' => 500000, 'rekening_id' => $this->rekening->id]);

    $this->actingAs($this->admin)
        ->put("/transaksi-penerimaan/{$tx->id}", [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-UPD-BAD',
            'realisasi' => 1000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['id' => $bku1->id, 'nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 500000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasErrors('bkus');

    expect((float) $tx->fresh()->realisasi)->toBe(500000.0);
});

test('rekening balance reflects transaksi bku through master penerimaan', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-REK',
            'realisasi' => 1000000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                ['nomor_bku' => 'BKU-001', 'tanggal_bku' => now()->format('Y-m-d'), 'nilai' => 1000000, 'rekening_id' => $this->rekening->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect((float) $this->rekening->totalPenerimaan())->toBe(1000000.0);
});
