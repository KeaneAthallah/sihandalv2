<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\TransferDana;
use App\Models\User;
use App\Services\KasService;

function transferFixture(): array
{
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $pengirim = SumberDana::create(['nama_sumber_dana' => 'DAK']);
    $penerima = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    // Kas masuk pada sumber dana pengirim.
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);
    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'rekening_id' => $rekening->id, 'target' => 100000000,
    ]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $pengirim->id,
        'realisasi' => 1000000,
        'tanggal' => now(),
    ]);

    return compact('admin', 'opd', 'pengirim', 'penerima');
}

test('transfer selesai ditolak saat saldo pengirim tidak mencukupi', function () {
    $f = transferFixture();

    $transfer = TransferDana::create([
        'nomor_transfer' => 'TF-0001/'.now()->year,
        'opd_id' => $f['opd']->id,
        'jumlah' => 2000000,
        'sumber_dana_pengirim_id' => $f['pengirim']->id,
        'sumber_dana_penerima_id' => $f['penerima']->id,
        'status' => 'diproses',
    ]);

    $this->actingAs($f['admin'])
        ->put("/transfer-dana/{$transfer->id}", [
            'opd_id' => $f['opd']->id,
            'jumlah' => 2000000,
            'sumber_dana_pengirim_id' => $f['pengirim']->id,
            'sumber_dana_penerima_id' => $f['penerima']->id,
            'status' => 'selesai',
        ])
        ->assertSessionHasErrors('jumlah');

    expect($transfer->fresh()->status)->toBe('diproses')
        ->and($transfer->fresh()->tanggal_selesai)->toBeNull();
});

test('saldo pengirim berkurang dan penerima bertambah setelah transfer selesai', function () {
    $f = transferFixture();

    $transfer = TransferDana::create([
        'nomor_transfer' => 'TF-0002/'.now()->year,
        'opd_id' => $f['opd']->id,
        'jumlah' => 400000,
        'sumber_dana_pengirim_id' => $f['pengirim']->id,
        'sumber_dana_penerima_id' => $f['penerima']->id,
        'status' => 'diproses',
    ]);

    $kas = app(KasService::class);

    expect($kas->saldoTersedia($f['opd']->id, $f['pengirim']->id))->toBe(1000000.0)
        ->and($kas->saldoTersedia($f['opd']->id, $f['penerima']->id))->toBe(0.0);

    $this->actingAs($f['admin'])
        ->put("/transfer-dana/{$transfer->id}", [
            'opd_id' => $f['opd']->id,
            'jumlah' => 400000,
            'sumber_dana_pengirim_id' => $f['pengirim']->id,
            'sumber_dana_penerima_id' => $f['penerima']->id,
            'status' => 'selesai',
        ])
        ->assertRedirect();

    expect($transfer->fresh()->status)->toBe('selesai')
        ->and($transfer->fresh()->tanggal_selesai)->not->toBeNull()
        ->and($kas->saldoTersedia($f['opd']->id, $f['pengirim']->id))->toBe(600000.0)
        ->and($kas->saldoTersedia($f['opd']->id, $f['penerima']->id))->toBe(400000.0);
});

test('draft dan diproses tidak mengubah saldo', function () {
    $f = transferFixture();

    $transfer = TransferDana::create([
        'nomor_transfer' => 'TF-0003/'.now()->year,
        'opd_id' => $f['opd']->id,
        'jumlah' => 400000,
        'sumber_dana_pengirim_id' => $f['pengirim']->id,
        'sumber_dana_penerima_id' => $f['penerima']->id,
        'status' => 'draft',
    ]);

    $kas = app(KasService::class);

    expect($kas->saldoTersedia($f['opd']->id, $f['pengirim']->id))->toBe(1000000.0);

    // Masih draft: belum ada perpindahan.
    $this->actingAs($f['admin'])
        ->put("/transfer-dana/{$transfer->id}", [
            'opd_id' => $f['opd']->id,
            'jumlah' => 400000,
            'sumber_dana_pengirim_id' => $f['pengirim']->id,
            'sumber_dana_penerima_id' => $f['penerima']->id,
            'status' => 'diproses',
        ]);

    expect($kas->saldoTersedia($f['opd']->id, $f['pengirim']->id))->toBe(1000000.0)
        ->and($kas->saldoTersedia($f['opd']->id, $f['penerima']->id))->toBe(0.0);
});
