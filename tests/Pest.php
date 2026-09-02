<?php

use App\Models\Belanja;
use App\Models\Dinas;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\Persetujuan;
use App\Models\PosisiKas;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\TransferDana;
use App\Models\Unit;
use App\Models\Upt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function seedFullDataset(): array
{
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A', 'total_pagu' => 2000000000]);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B', 'total_pagu' => 1000000000]);

    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'Dana Alokasi Umum (DAU)']);

    $rekening = Rekening::create(['kode' => '4.1.1', 'nama' => 'Kas Daerah', 'tipe' => 'kas', 'saldo' => 150000000]);
    Rekening::create(['kode' => '4.1.2', 'nama' => 'Pendapatan Pajak', 'tipe' => 'pendapatan', 'saldo' => 0]);

    $program = Program::create([
        'kode_program' => '1.2',
        'nama_program' => 'Program Penunjang Urusan',
        'opd_id' => $opd->id,
    ]);

    $kegiatan = Kegiatan::create([
        'program_id' => $program->id,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'kode_kegiatan' => '1.2.3',
        'nama_kegiatan' => 'Penyelenggaraan Kegiatan',
        'kode_rekening' => '5.2.1',
        'nama_rekening' => 'Belanja Operasional',
        'pagu' => 500000000,
        'realisasi' => 100000000,
        'persentase' => 20,
    ]);

    $dinas = Dinas::create(['kode' => 'DIN-1', 'nama' => 'Dinas '.$opd->nama, 'opd_id' => $opd->id]);
    $unit = Unit::create(['kode' => 'UNIT-1', 'nama' => 'Unit '.$opd->nama, 'opd_id' => $opd->id, 'dinas_id' => $dinas->id]);
    $upt = Upt::create(['kode' => 'UPT-1', 'nama' => 'UPT '.$opd->nama, 'opd_id' => $opd->id]);

    $subKegiatan = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id,
        'kode_sub_kegiatan' => '1.2.3.1',
        'nama_sub_kegiatan' => 'Sub Kegiatan',
        'pagu' => 200000000,
        'realisasi' => 100000000,
        'persentase' => 50,
    ]);

    $belanja = Belanja::create([
        'sub_kegiatan_id' => $subKegiatan->id,
        'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id,
        'opd_id' => $opd->id,
        'pagu' => 200000000,
        'realisasi' => 100000000,
        'dana_di_commit' => 0,
    ]);

    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id,
        'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id,
        'kode_sumber_dana' => 'DAU',
        'nama_sumber_dana' => 'Dana Alokasi Umum (DAU)',
        'target' => 1000000000,
    ]);

    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'realisasi' => 400000000,
        'tanggal' => now(),
        'keterangan' => 'Penerimaan awal',
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

    return compact(
        'opd', 'opdB', 'admin', 'user', 'sumberDana', 'program', 'rekening',
        'permintaanDraft', 'permintaanMenunggu', 'kegiatan', 'subKegiatan',
        'belanja', 'dinas', 'unit', 'upt'
    );
}
