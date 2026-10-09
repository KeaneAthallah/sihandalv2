<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\User;

test('admin can view the dashboard with financial summaries', function () {
    $d = seedFullDataset();

    $this->actingAs($d['admin'])
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertSee('Total Pagu')
        ->assertSee('Realisasi Penerimaan')
        ->assertSee('Pagu Tersedia')
        ->assertSee('Tren Bulanan')
        ->assertSee('Top OPD Realisasi')
        ->assertSee('Realisasi per Program')
        ->assertSee('Dana Alokasi Umum (DAU)')
        ->assertViewHas('budget', fn ($budget) => (float) $budget['pagu'] === 200000000.0
            && (float) $budget['realisasi'] === 100000000.0
            && (float) $budget['commit'] === 0.0
            && (float) $budget['available'] === 100000000.0)
        ->assertViewHas('kas', fn ($kas) => (float) $kas['kas_penerimaan'] === 400000000.0
            && (float) $kas['kas_pengeluaran'] === 100000000.0
            && (float) $kas['saldo_kas'] === 300000000.0)
        ->assertViewHas('permintaanCounts', fn ($counts) => $counts['draft'] === 1
            && $counts['menunggu'] === 1
            && $counts['disetujui'] === 1
            && $counts['ditolak'] === 0);
});

test('admin dashboard renders the seeded money figures', function () {
    $d = seedFullDataset();

    // Pagu 200 juta -> 0,20 miliar; penerimaan 400 juta -> 0,40 miliar.
    $this->actingAs($d['admin'])
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertSee('Rp 0,20')
        ->assertSee('Rp 0,40');
});

test('opd user only sees their own opd data', function () {
    $d = seedFullDataset();

    PermintaanDana::create([
        'nomor_permintaan' => 'PD-OPDB-UNIQUE',
        'opd_id' => $d['opdB']->id,
        'sumber_dana_id' => $d['sumberDana']->id,
        'sumber_dana' => 'Dana Alokasi Umum (DAU)',
        'jumlah' => 9000000,
        'keperluan' => 'Milik OPD B',
        'status' => 'menunggu',
    ]);

    $this->actingAs($d['user'])
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertSee('PD-0001/'.now()->year)
        ->assertDontSee('PD-OPDB-UNIQUE')
        ->assertSee('Kas Saya per Sumber Dana')
        ->assertDontSee('Top OPD Realisasi')
        ->assertViewHas('permintaanCounts', fn ($counts) => $counts['menunggu'] === 1);

    // Admin sees the other OPD's request too.
    $this->actingAs($d['admin'])
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertSee('PD-OPDB-UNIQUE');
});

test('dashboard reflects newly created penerimaan and pengeluaran', function () {
    $opd = Opd::create(['kode' => 'OPD-X', 'nama' => 'Dinas X']);
    $admin = User::factory()->admin()->create();
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan', 'tipe' => 'pendapatan']);

    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
        'target' => 1000000000,
    ]);

    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDana->id,
        'realisasi' => 750000000,
        'tanggal' => now(),
    ]);

    Pengeluaran::create([
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 250000000,
        'keperluan' => 'Operasional',
        'tanggal' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertSee('Rp 0,75')
        ->assertViewHas('kas', fn ($kas) => (float) $kas['kas_penerimaan'] === 750000000.0
            && (float) $kas['kas_pengeluaran'] === 250000000.0
            && (float) $kas['saldo_kas'] === 500000000.0);
});
