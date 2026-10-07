<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\PermintaanDana;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use App\Services\KasService;

function kasLimitFixture(): array
{
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);

    $program = Program::create(['kode_program' => '1.1', 'nama_program' => 'Program A', 'opd_id' => $opd->id]);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id, 'kode_kegiatan' => '1.1.1', 'nama_kegiatan' => 'Kegiatan A',
    ]);
    $subKegiatan = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id, 'kode_sub_kegiatan' => '1.1.1.1', 'nama_sub_kegiatan' => 'Sub A',
    ]);
    $belanja = Belanja::create([
        'sub_kegiatan_id' => $subKegiatan->id, 'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id, 'opd_id' => $opd->id,
        'pagu' => 1000000, 'realisasi' => 0, 'dana_di_commit' => 0,
    ]);

    return compact('opd', 'user', 'sumberDana', 'kegiatan', 'subKegiatan', 'belanja');
}

function buatPermintaanDraft(array $fixture, int $jumlah): PermintaanDana
{
    return PermintaanDana::create([
        'nomor_permintaan' => 'PD-'.uniqid().'/'.now()->year,
        'opd_id' => $fixture['opd']->id,
        'sumber_dana_id' => $fixture['sumberDana']->id,
        'sumber_dana' => $fixture['sumberDana']->nama_sumber_dana,
        'kegiatan_id' => $fixture['kegiatan']->id,
        'sub_kegiatan_id' => $fixture['subKegiatan']->id,
        'belanja_id' => $fixture['belanja']->id,
        'jumlah' => $jumlah,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);
}

function kasMasuk(array $fixture, int $nilai): void
{
    $penerimaan = Penerimaan::create([
        'opd_id' => $fixture['opd']->id, 'target' => 100000000,
    ]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $fixture['sumberDana']->id,
        'realisasi' => $nilai,
        'tanggal' => now(),
    ]);
}

test('submit ditolak saat kas tidak mencukupi', function () {
    $f = kasLimitFixture();
    $permintaan = buatPermintaanDraft($f, 400000);

    // Tanpa penerimaan, kas efektif = 0.
    expect(app(KasService::class)->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(0.0);

    $this->actingAs($f['user'])
        ->post("/permintaan-dana/{$permintaan->id}/submit")
        ->assertRedirect();

    expect($permintaan->fresh()->status)->toBe('draft')
        ->and((float) $f['belanja']->fresh()->dana_di_commit)->toBe(0.0);
});

test('submit diterima saat kas mencukupi', function () {
    $f = kasLimitFixture();
    kasMasuk($f, 500000);
    $permintaan = buatPermintaanDraft($f, 400000);

    $this->actingAs($f['user'])
        ->post("/permintaan-dana/{$permintaan->id}/submit")
        ->assertRedirect();

    expect($permintaan->fresh()->status)->toBe('menunggu')
        ->and((float) $f['belanja']->fresh()->dana_di_commit)->toBe(400000.0);
});

test('submit kedua ditolak saat kas sudah direservasi permintaan lain', function () {
    $f = kasLimitFixture();
    kasMasuk($f, 500000);

    $pertama = buatPermintaanDraft($f, 400000);
    $this->actingAs($f['user'])->post("/permintaan-dana/{$pertama->id}/submit");

    // Kas masuk 500k, 400k sudah di-reservasi:
    // hanya tersisa 100k untuk permintaan berikutnya.
    $kedua = buatPermintaanDraft($f, 200000);
    $this->actingAs($f['user'])
        ->post("/permintaan-dana/{$kedua->id}/submit")
        ->assertRedirect();

    expect($kedua->fresh()->status)->toBe('draft')
        ->and((float) $f['belanja']->fresh()->dana_di_commit)->toBe(400000.0);
});

test('kas di_commit dilepas saat permintaan ditolak', function () {
    $f = kasLimitFixture();
    kasMasuk($f, 500000);
    $permintaan = buatPermintaanDraft($f, 400000);

    $this->actingAs($f['user'])->post("/permintaan-dana/{$permintaan->id}/submit");

    expect(app(KasService::class)->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(100000.0);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->post("/persetujuan/{$permintaan->id}/tolak");

    expect($permintaan->fresh()->status)->toBe('ditolak')
        ->and((float) $f['belanja']->fresh()->dana_di_commit)->toBe(0.0)
        ->and(app(KasService::class)->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(500000.0);
});

test('submit ditolak saat jumlah melebihi pagu tersisa meski kas cukup', function () {
    $f = kasLimitFixture();
    kasMasuk($f, 5000000);

    // Pagu belanja hanya 1jt.
    $permintaan = buatPermintaanDraft($f, 1500000);

    $this->actingAs($f['user'])
        ->post("/permintaan-dana/{$permintaan->id}/submit")
        ->assertRedirect();

    expect($permintaan->fresh()->status)->toBe('draft')
        ->and((float) $f['belanja']->fresh()->dana_di_commit)->toBe(0.0);
});
