<?php

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\Persetujuan;
use App\Models\PosisiKas;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TransferDana;
use App\Models\User;

function seedFullDataset(): array
{
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A', 'total_pagu' => 2000000000]);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B', 'total_pagu' => 1000000000]);

    $sumberDana = SumberDana::create(['opd_id' => $opd->id, 'nama_sumber_dana' => 'Dana Alokasi Umum (DAU)', 'pagu' => 1000000000, 'realisasi' => 400000000, 'persentase' => 40]);
    SumberDana::create(['opd_id' => $opdB->id, 'nama_sumber_dana' => 'Dana Alokasi Umum (DAU)', 'pagu' => 800000000, 'realisasi' => 200000000, 'persentase' => 25]);

    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Kas Daerah', 'tipe' => 'kas', 'saldo' => 150000000]);
    Rekening::create(['kode' => '4.1.2', 'nama' => 'Pendapatan Pajak', 'tipe' => 'pendapatan', 'saldo' => 0]);

    Program::create([
        'opd_id' => $opd->id,
        'kode_kegiatan' => '1.2.3',
        'nama_kegiatan' => 'Penyelenggaraan Kegiatan',
        'sumber_dana' => 'Dana Alokasi Umum (DAU)',
        'pagu' => 500000000,
        'realisasi' => 100000000,
        'persentase' => 20,
    ]);

    Penerimaan::create([
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
        'kode_sumber_dana' => 'DAU',
        'nama_sumber_dana' => 'Dana Alokasi Umum (DAU)',
        'target' => 1000000000,
        'realisasi' => 400000000,
        'persentase' => 40,
        'tanggal' => now(),
    ]);

    Pengeluaran::create([
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
        'kode_kegiatan' => '1.2.3',
        'nama_kegiatan' => 'Penyelenggaraan Kegiatan',
        'sumber_dana' => 'Dana Alokasi Umum (DAU)',
        'anggaran' => 500000000,
        'realisasi' => 100000000,
        'persentase' => 20,
        'tanggal' => now(),
    ]);

    PosisiKas::create([
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
        'tanggal' => now(),
        'saldo_awal' => 100000000,
        'penerimaan' => 50000000,
        'pengeluaran' => 20000000,
        'saldo_akhir' => 130000000,
    ]);

    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $permintaanDraft = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'Dana Alokasi Umum (DAU)',
        'jumlah' => 50000000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $permintaanMenunggu = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0002/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'Dana Alokasi Umum (DAU)',
        'jumlah' => 30000000,
        'keperluan' => 'Operasional 2',
        'status' => 'menunggu',
    ]);

    PermintaanDana::create([
        'nomor_permintaan' => 'PD-0003/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'Dana Alokasi Umum (DAU)',
        'jumlah' => 20000000,
        'keperluan' => 'Operasional 3',
        'status' => 'disetujui',
    ]);

    Persetujuan::create([
        'permintaan_dana_id' => $permintaanMenunggu->id,
        'user_id' => $admin->id,
        'keputusan' => 'disetujui',
        'catatan' => 'Ok',
    ]);

    TransferDana::create([
        'nomor_transfer' => 'TRF-0001',
        'opd_id' => $opd->id,
        'jumlah' => 50000000,
        'sumber_dana' => 'Dana Alokasi Umum (DAU)',
        'keterangan' => 'Transfer',
        'status' => 'selesai',
        'tanggal' => now(),
    ]);

    return compact('opd', 'opdB', 'admin', 'user', 'sumberDana', 'rekening', 'permintaanDraft', 'permintaanMenunggu');
}

test('all pages render for admin', function () {
    $d = seedFullDataset();

    $this->actingAs($d['admin']);

    $this->get('/dashboard')->assertSuccessful();
    $this->get('/opd')->assertSuccessful();
    $this->get("/opd/{$d['opd']->id}")->assertSuccessful();
    $this->get('/sumber-dana')->assertSuccessful();
    $this->get('/sumber-dana/create')->assertSuccessful();
    $this->get("/sumber-dana/{$d['sumberDana']->id}/edit")->assertSuccessful();
    $this->get('/rekening-kas')->assertSuccessful();
    $this->get('/rekening-kas/create')->assertSuccessful();
    $this->get("/rekening-kas/{$d['rekening']->id}/edit")->assertSuccessful();
    $this->get('/program-kegiatan')->assertSuccessful();
    $this->get('/program-kegiatan/create')->assertSuccessful();
    $this->get("/program-kegiatan/{$d['rekening']->id}/edit")->assertSuccessful();
    $this->get('/penerimaan')->assertSuccessful();
    $this->get('/penerimaan/create')->assertSuccessful();
    $this->get('/pengeluaran')->assertSuccessful();
    $this->get('/pengeluaran/create')->assertSuccessful();
    $this->get('/posisi-kas')->assertSuccessful();
    $this->get('/posisi-kas/create')->assertSuccessful();
    $this->get('/permintaan-dana')->assertSuccessful();
    $this->get('/permintaan-dana/create')->assertSuccessful();
    $this->get("/permintaan-dana/{$d['permintaanDraft']->id}/edit")->assertSuccessful();
    $this->get('/persetujuan')->assertSuccessful();
    $this->get('/transfer-dana')->assertSuccessful();
    $this->get('/transfer-dana/create')->assertSuccessful();
    $this->get('/laporan-penerimaan')->assertSuccessful();
    $this->get('/laporan-pengeluaran')->assertSuccessful();
    $this->get('/laporan-posisi-kas')->assertSuccessful();
    $this->get('/rekap-permintaan-dana')->assertSuccessful();
    $this->get('/pengaturan')->assertSuccessful();
    $this->get('/user-management')->assertSuccessful();
    $this->get('/user-management/create')->assertSuccessful();
    $this->get("/user-management/{$d['user']->id}/edit")->assertSuccessful();
    $this->get('/profile')->assertSuccessful();
});

test('all pages render for opd user', function () {
    $d = seedFullDataset();

    $this->actingAs($d['user']);

    $pages = [
        '/dashboard', '/opd', "/opd/{$d['opd']->id}",
        '/sumber-dana', '/sumber-dana/create', "/sumber-dana/{$d['sumberDana']->id}/edit",
        '/rekening-kas', '/rekening-kas/create',
        '/program-kegiatan', '/program-kegiatan/create',
        '/penerimaan', '/penerimaan/create',
        '/pengeluaran', '/pengeluaran/create',
        '/posisi-kas', '/posisi-kas/create',
        '/permintaan-dana', '/permintaan-dana/create', "/permintaan-dana/{$d['permintaanDraft']->id}/edit",
        '/persetujuan',
        '/transfer-dana', '/transfer-dana/create',
        '/laporan-penerimaan', '/laporan-pengeluaran', '/laporan-posisi-kas',
        '/rekap-permintaan-dana', '/pengaturan', '/profile',
    ];

    foreach ($pages as $page) {
        $response = $this->get($page);
        if ($response->status() >= 300) {
            throw new RuntimeException("FAILED: {$page} returned {$response->status()}");
        }
        $response->assertSuccessful($page);
    }
});

test('opd user cannot access admin only pages', function () {
    $d = seedFullDataset();

    $this->actingAs($d['user'])
        ->get('/user-management')
        ->assertForbidden();

    $this->actingAs($d['user'])
        ->get('/user-management/create')
        ->assertForbidden();
});

