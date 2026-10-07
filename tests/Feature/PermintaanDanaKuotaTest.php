<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\Setting;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use App\Services\KasService;

function kuotaFixture(): array
{
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $admin = User::factory()->admin()->create();
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan PAD', 'tipe' => 'pendapatan']);
    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'rekening_id' => $rekening->id, 'target' => 100000000,
    ]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDana->id,
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);

    return compact('opd', 'user', 'admin', 'sumberDana');
}

test('kuota penerimaan default 100 persen', function () {
    $f = kuotaFixture();

    expect((float) Setting::get('penerimaan_kuota_persen', 100))->toBe(100.0)
        ->and(app(KasService::class)->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(500000.0);
});

test('opd dibatasi kuota penerimaan', function () {
    $f = kuotaFixture();
    Setting::set('penerimaan_kuota_persen', '50');

    // 50% dari kas masuk 500k = 250k yang boleh dipakai OPD.
    expect(app(KasService::class)->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(250000.0)
        ->and(app(KasService::class)->saldo($f['opd']->id, $f['sumberDana']->id))->toBe(500000.0);
});

test('admin bebas kuota penerimaan', function () {
    $f = kuotaFixture();
    Setting::set('penerimaan_kuota_persen', '50');

    expect(app(KasService::class)->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['admin']))->toBe(500000.0);
});

test('pengaturan menyimpan kuota penerimaan antara 0 dan 100', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put('/pengaturan', ['penerimaan_kuota_persen' => 75])
        ->assertRedirect();

    expect((float) Setting::get('penerimaan_kuota_persen', 100))->toBe(75.0);

    $this->actingAs($admin)
        ->from('/pengaturan')
        ->put('/pengaturan', ['penerimaan_kuota_persen' => 150])
        ->assertSessionHasErrors('penerimaan_kuota_persen');

    $this->actingAs($admin)
        ->from('/pengaturan')
        ->put('/pengaturan', ['penerimaan_kuota_persen' => -5])
        ->assertSessionHasErrors('penerimaan_kuota_persen');

    expect((float) Setting::get('penerimaan_kuota_persen', 100))->toBe(75.0);
});
