<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
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

test('nomor registrasi is auto-generated with at least 5 digits and year suffix', function () {
    $this->actingAs($this->admin)
        ->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'realisasi' => 500000,
            'tanggal' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasNoErrors();

    $tx = TransaksiPenerimaan::first();

    expect($tx->nomor_registrasi)->toBe('REG-00001/'.now()->year);
});

test('nomor registrasi sequence resets per fiscal year based on transaction date', function () {
    $lastYear = now()->subYear()->year;

    $this->actingAs($this->admin);

    foreach ([
        "$lastYear-12-31" => 100000,
        "$lastYear-10-01" => 200000,
        now()->format('Y-m-d') => 300000,
    ] as $tanggal => $realisasi) {
        $this->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'realisasi' => $realisasi,
            'tanggal' => $tanggal,
        ])->assertSessionHasNoErrors();
    }

    $numbers = TransaksiPenerimaan::orderBy('id')->pluck('nomor_registrasi')->all();

    expect($numbers)->toBe([
        "REG-00001/{$lastYear}",
        "REG-00002/{$lastYear}",
        'REG-00001/'.now()->year,
    ]);
});

test('auto-generated nomor registrasi is sequential and unique', function () {
    $this->actingAs($this->admin);

    foreach ([1, 2, 3, 4, 5] as $i) {
        $this->post('/transaksi-penerimaan', [
            'penerimaan_id' => $this->penerimaan->id,
            'realisasi' => 100000 * $i,
            'tanggal' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();
    }

    $numbers = TransaksiPenerimaan::orderBy('id')->pluck('nomor_registrasi');

    expect($numbers->unique()->count())->toBe(5)
        ->and($numbers->every(fn ($n) => (bool) preg_match('/^REG-\d{5}\/\d{4}$/', $n)))->toBeTrue()
        ->and($numbers->first())->toBe('REG-00001/'.now()->year)
        ->and($numbers->last())->toBe('REG-00005/'.now()->year);
});

test('auto-generated nomor registrasi is immutable on edit', function () {
    $tx = TransaksiPenerimaan::create([
        'penerimaan_id' => $this->penerimaan->id,
        'nomor_registrasi' => 'REG-00001/'.now()->year,
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);

    $this->actingAs($this->admin)
        ->put("/transaksi-penerimaan/{$tx->id}", [
            'penerimaan_id' => $this->penerimaan->id,
            'nomor_registrasi' => 'REG-DIEDIT',
            'realisasi' => 750000,
            'tanggal' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasNoErrors();

    expect($tx->fresh()->nomor_registrasi)->toBe('REG-00001/'.now()->year)
        ->and($tx->fresh()->nomor_registrasi)->not->toBe('REG-DIEDIT');
});
