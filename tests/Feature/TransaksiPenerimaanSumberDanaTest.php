<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use App\Services\KasService;

test('transaksi penerimaan mengharuskan sumber dana dipilih', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);
    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'rekening_id' => $rekening->id, 'target' => 1000000,
    ]);

    $this->actingAs($admin)
        ->from('/transaksi-penerimaan/create')
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaan->id,
            'realisasi' => 100000,
            'tanggal' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors('sumber_dana_id');

    expect(TransaksiPenerimaan::count())->toBe(0);
});

test('transaksi penerimaan menyimpan sumber dana pada baris penerimaan', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'Dana Alokasi Umum (DAU)']);
    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'rekening_id' => $rekening->id, 'target' => 1000000,
    ]);

    $this->actingAs($admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $penerimaan->id,
            'sumber_dana_id' => $sumberDana->id,
            'realisasi' => 250000,
            'tanggal' => now()->format('Y-m-d'),
            'bkus' => [
                [
                    'nomor_bku' => 'BKU-1',
                    'tanggal_bku' => now()->format('Y-m-d'),
                    'nilai' => 250000,
                ],
            ],
        ]);

    $this->assertDatabaseHas('transaksi_penerimaans', [
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDana->id,
        'realisasi' => 250000,
    ]);
});

test('halaman transaksi penerimaan menampilkan nama sumber dana per transaksi', function () {
    $d = seedFullDataset();

    $this->actingAs($d['admin'])
        ->get('/transaksi-penerimaan')
        ->assertSuccessful()
        ->assertSee($d['sumberDana']->nama_sumber_dana);
});

test('kas masuk per sumber dana mengikuti transaksi penerimaan', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);
    $sumberDanaA = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $sumberDanaB = SumberDana::create(['nama_sumber_dana' => 'DAK']);
    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'rekening_id' => $rekening->id, 'target' => 1000000,
    ]);

    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDanaA->id,
        'realisasi' => 300000,
        'tanggal' => now(),
    ]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDanaB->id,
        'realisasi' => 150000,
        'tanggal' => now(),
    ]);

    $kas = app(KasService::class);

    expect($kas->ringkasan($opd->id, $sumberDanaA->id)['masuk'])->toBe(300000.0)
        ->and($kas->ringkasan($opd->id, $sumberDanaB->id)['masuk'])->toBe(150000.0)
        ->and($kas->ringkasan($opd->id)['masuk'])->toBe(450000.0);
});

test('transaksi legacy tanpa sumber dana masuk bucket Tanpa Sumber Dana', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'rekening_id' => $rekening->id, 'target' => 1000000,
    ]);

    // Baris legacy tanpa sumber dana...
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'realisasi' => 250000,
        'tanggal' => now(),
    ]);
    // ...dan baris baru yang sudah terikat.
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDana->id,
        'realisasi' => 100000,
        'tanggal' => now(),
    ]);

    $ringkasan = app(KasService::class)->perSumberDana($opd->id);

    $legacy = collect($ringkasan)->firstWhere('sumber_dana_id', 0);
    expect($legacy['nama'])->toBe('Tanpa Sumber Dana')
        ->and($legacy['masuk'])->toBe(250000.0)
        ->and($legacy['saldo'])->toBe(250000.0);

    $dau = collect($ringkasan)->firstWhere('sumber_dana_id', $sumberDana->id);
    expect($dau['masuk'])->toBe(100000.0);

    // Bucket tidak muncul bila tidak ada transaksi legacy.
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    expect(collect(app(KasService::class)->perSumberDana($opdB->id))->firstWhere('sumber_dana_id', 0))->toBeNull();
});
