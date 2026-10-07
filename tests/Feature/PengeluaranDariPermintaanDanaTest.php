<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use App\Services\KasService;
use App\Services\PermintaanDanaService;

function pengeluaranDanaFixture(): array
{
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $admin = User::factory()->admin()->create();
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

    // Kas masuk agar permintaan dana dapat dikomit.
    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'target' => 100000000,
    ]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDana->id,
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);

    return compact('opd', 'admin', 'user', 'sumberDana', 'kegiatan', 'subKegiatan', 'belanja');
}

function permintaanDanaDisetujui(array $f, int $jumlah = 400000, string $keperluan = 'Pengadaan barang'): PermintaanDana
{
    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-'.uniqid().'/'.now()->year,
        'opd_id' => $f['opd']->id,
        'sumber_dana_id' => $f['sumberDana']->id,
        'sumber_dana' => $f['sumberDana']->nama_sumber_dana,
        'kegiatan_id' => $f['kegiatan']->id,
        'sub_kegiatan_id' => $f['subKegiatan']->id,
        'belanja_id' => $f['belanja']->id,
        'jumlah' => $jumlah,
        'keperluan' => $keperluan,
        'status' => 'draft',
    ]);

    $permintaanDanaService = app(PermintaanDanaService::class);
    $permintaanDanaService->submit($permintaan, $f['user']);

    return $permintaanDanaService->approve($permintaan, $f['admin']);
}

test('pengeluaran hanya dapat dibuat dari permintaan dana disetujui', function () {
    $f = pengeluaranDanaFixture();

    // PD berstatus draft (belum diajukan).
    $draft = PermintaanDana::create([
        'nomor_permintaan' => 'PD-DRAFT/'.now()->year,
        'opd_id' => $f['opd']->id,
        'sumber_dana_id' => $f['sumberDana']->id,
        'sumber_dana' => 'DAU',
        'kegiatan_id' => $f['kegiatan']->id,
        'sub_kegiatan_id' => $f['subKegiatan']->id,
        'belanja_id' => $f['belanja']->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($f['admin'])
        ->post('/pengeluaran', [
            'permintaan_dana_id' => $draft->id,
            'no_sp2d' => 'SP2D-1',
        ])
        ->assertSessionHasErrors('permintaan_dana_id');

    expect(Pengeluaran::count())->toBe(0);
});

test('field pengeluaran di-mirror read-only dari permintaan dana', function () {
    $f = pengeluaranDanaFixture();
    $permintaan = permintaanDanaDisetujui($f, 400000, 'Pengadaan barang');

    $this->actingAs($f['admin'])
        ->post('/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            // Input jahat: jumlah dan keperluan diubah-ubah,
            // harus diabaikan server.
            'jumlah' => 1,
            'keperluan' => 'dihalusi',
            'no_sp2d' => 'SP2D/2026/001',
            'tanggal_sp2d' => now()->format('Y-m-d'),
            'tanggal' => now()->format('Y-m-d'),
        ]);

    $this->assertDatabaseHas('pengeluarans', [
        'permintaan_dana_id' => $permintaan->id,
        'opd_id' => $f['opd']->id,
        'sumber_dana_id' => $f['sumberDana']->id,
        'belanja_id' => $f['belanja']->id,
        'jumlah' => 400000,
        'keperluan' => 'Pengadaan barang',
        'no_sp2d' => 'SP2D/2026/001',
    ]);
});

test('approve memindahkan dana di_commit menjadi realisasi belanja', function () {
    $f = pengeluaranDanaFixture();

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-APR/'.now()->year,
        'opd_id' => $f['opd']->id,
        'sumber_dana_id' => $f['sumberDana']->id,
        'sumber_dana' => 'DAU',
        'kegiatan_id' => $f['kegiatan']->id,
        'sub_kegiatan_id' => $f['subKegiatan']->id,
        'belanja_id' => $f['belanja']->id,
        'jumlah' => 400000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $service = app(PermintaanDanaService::class);
    $service->submit($permintaan, $f['user']);

    expect((float) $f['belanja']->fresh()->dana_di_commit)->toBe(400000.0)
        ->and((float) $f['belanja']->fresh()->realisasi)->toBe(0.0);

    $service->approve($permintaan, $f['admin']);

    expect((float) $f['belanja']->fresh()->realisasi)->toBe(400000.0)
        ->and((float) $f['belanja']->fresh()->dana_di_commit)->toBe(0.0);
});

test('pengeluaran melepas reservasi kas dan mencatat kas keluar', function () {
    $f = pengeluaranDanaFixture();
    $permintaan = permintaanDanaDisetujui($f, 400000);

    $kas = app(KasService::class);

    // Reservasi masih dipegang setelah disetujui.
    expect($kas->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(100000.0);

    $this->actingAs($f['admin'])
        ->post('/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            'no_sp2d' => 'SP2D/2026/002',
        ]);

    // Reservasi dilepas, kas keluar tercatat:
    // 500k masuk - 400k keluar = 100k efektif.
    expect($kas->ringkasan($f['opd']->id, $f['sumberDana']->id)['keluar'])->toBe(400000.0)
        ->and($kas->saldoEfektif($f['opd']->id, $f['sumberDana']->id, $f['user']))->toBe(100000.0);
});

test('permintaan dana tidak dapat dibuatkan pengeluaran dua kali', function () {
    $f = pengeluaranDanaFixture();
    $permintaan = permintaanDanaDisetujui($f, 400000);

    $this->actingAs($f['admin'])
        ->post('/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            'no_sp2d' => 'SP2D/2026/003',
        ]);

    $this->actingAs($f['admin'])
        ->post('/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            'no_sp2d' => 'SP2D/2026/004',
        ])
        ->assertSessionHasErrors('permintaan_dana_id');

    expect(Pengeluaran::where('permintaan_dana_id', $permintaan->id)->count())->toBe(1);
});

test('opd user tidak dapat membuat pengeluaran dari permintaan dana', function () {
    $f = pengeluaranDanaFixture();
    $permintaan = permintaanDanaDisetujui($f, 400000);

    $this->actingAs($f['user'])
        ->post('/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            'no_sp2d' => 'SP2D/2026/005',
        ])
        ->assertSessionHasErrors('permintaan_dana_id');

    expect(Pengeluaran::count())->toBe(0);
});
