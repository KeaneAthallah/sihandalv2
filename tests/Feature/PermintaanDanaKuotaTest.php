<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\Setting;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use App\Services\KasService;
use Illuminate\Support\Facades\DB;

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
    $key = KasService::kuotaKey($f['sumberDana']->id);

    expect(Setting::get($key, 100))->toBe(100)
        ->and(app(KasService::class)->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(500000.0);
});

test('opd dibatasi kuota penerimaan per sumber dana', function () {
    $f = kuotaFixture();
    Setting::set(KasService::kuotaKey($f['sumberDana']->id), '50');

    // 50% dari kas masuk 500k = 250k yang boleh dipakai OPD.
    expect(app(KasService::class)->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(250000.0)
        ->and(app(KasService::class)->saldo($f['opd']->id, $f['sumberDana']->id))->toBe(500000.0);
});

test('kuota hanya berlaku untuk sumber dana terkait', function () {
    $f = kuotaFixture();
    $service = app(KasService::class);

    $sumberB = SumberDana::create(['nama_sumber_dana' => 'DAK']);
    $penerimaan = Penerimaan::where('opd_id', $f['opd']->id)->first();
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberB->id,
        'realisasi' => 200000,
        'tanggal' => now(),
    ]);

    Setting::set(KasService::kuotaKey($f['sumberDana']->id), '50');

    // Sumber dana yang sama berlaku untuk semua OPD.
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $userB = User::factory()->create(['role' => 'opd', 'opd_id' => $opdB->id]);
    $penerimaanB = Penerimaan::create(['opd_id' => $opdB->id, 'target' => 1000000]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaanB->id,
        'sumber_dana_id' => $f['sumberDana']->id,
        'realisasi' => 300000,
        'tanggal' => now(),
    ]);

    expect($service->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(250000.0)
        // ...sumber dana lain tidak terpengaruh...
        ->and($service->saldoEfektif($f['opd']->id, $sumberB->id, $f['user']))->toBe(200000.0)
        // ...dan OPD lain pada sumber dana yang sama ikut terpotong.
        ->and($service->saldoEfektif($opdB->id, $f['sumberDana']->id, $userB))->toBe(150000.0);
});

test('admin bebas kuota penerimaan', function () {
    $f = kuotaFixture();
    Setting::set(KasService::kuotaKey($f['sumberDana']->id), '50');

    expect(app(KasService::class)->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['admin']))->toBe(500000.0);
});

test('masuk efektif mengikuti kuota penerimaan', function () {
    $f = kuotaFixture();

    expect(app(KasService::class)->masukEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(500000.0);

    Setting::set(KasService::kuotaKey($f['sumberDana']->id), '50');

    // 50% dari kas masuk 500k = 250k yang ditampilkan
    // sebagai penerimaan untuk OPD; admin tetap penuh.
    expect(app(KasService::class)->masukEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(250000.0)
        ->and(app(KasService::class)->masukEfektif($f['opd']->id, $f['sumberDana']->id, $f['admin']))->toBe(500000.0);
});

test('ringkasan dihitung sekali per pasangan opd, sumber dana, dan actor', function () {
    $f = kuotaFixture();
    $service = app(KasService::class);

    DB::enableQueryLog();
    $service->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']);
    $first = count(DB::getQueryLog());

    DB::flushQueryLog();
    $service->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']);
    $repeat = count(DB::getQueryLog());

    expect($first)->toBeGreaterThan(0)
        ->and($repeat)->toBe(0);
});

test('memo ringkasan memisahkan sumber dana dan peran actor', function () {
    $f = kuotaFixture();
    $service = app(KasService::class);

    // Second sumber dana with its own transaction.
    $sumberB = SumberDana::create(['nama_sumber_dana' => 'DAK']);
    $penerimaan = Penerimaan::where('opd_id', $f['opd']->id)->first();
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberB->id,
        'realisasi' => 100000,
        'tanggal' => now(),
    ]);

    Setting::set(KasService::kuotaKey($f['sumberDana']->id), '50');
    Setting::set(KasService::kuotaKey($sumberB->id), '50');

    // Warm the cache for an OPD user on the first sumber dana.
    $service->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']);

    expect($service->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(250000.0)
        ->and($service->saldoEfektif($f['opd']->id, $sumberB->id, $f['user']))->toBe(50000.0)
        // Agregat menimbang tiap sumber dana dengan kuotanya.
        ->and($service->saldoEfektif($f['opd']->id, null, $f['user']))->toBe(300000.0)
        ->and($service->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['admin']))->toBe(500000.0);
});

test('halaman pengaturan menampilkan kuota per sumber dana', function () {
    $f = kuotaFixture();

    $this->actingAs($f['admin'])
        ->get('/pengaturan')
        ->assertSuccessful()
        ->assertSee($f['sumberDana']->nama_sumber_dana)
        ->assertSee('name="kuota['.$f['sumberDana']->id.']"', false);
});

test('pengaturan menyimpan kuota penerimaan per sumber dana antara 0 dan 100', function () {
    $f = kuotaFixture();
    $key = KasService::kuotaKey($f['sumberDana']->id);

    $this->actingAs($f['admin'])
        ->put('/pengaturan', ['kuota' => [$f['sumberDana']->id => 75]])
        ->assertRedirect();

    expect((float) Setting::get($key, 100))->toBe(75.0);

    $this->actingAs($f['admin'])
        ->from('/pengaturan')
        ->put('/pengaturan', ['kuota' => [$f['sumberDana']->id => 150]])
        ->assertSessionHasErrors('kuota.'.$f['sumberDana']->id);

    $this->actingAs($f['admin'])
        ->from('/pengaturan')
        ->put('/pengaturan', ['kuota' => [$f['sumberDana']->id => -5]])
        ->assertSessionHasErrors('kuota.'.$f['sumberDana']->id);

    expect((float) Setting::get($key, 100))->toBe(75.0);
});
