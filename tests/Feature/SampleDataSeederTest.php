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
use App\Models\TransaksiPenerimaan;
use App\Models\TransferDana;
use Database\Seeders\SampleDataSeeder;

test('sample data seeder berjalan di atas skema terbaru', function () {
    Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);

    $this->artisan('db:seed', ['--class' => SampleDataSeeder::class])->assertExitCode(0);

    // Transaksi penerimaan wajib terikat sumber dana,
    // dan transfer wajib menyebut dua sumber dana berbeda.
    expect(Belanja::count())->toBeGreaterThan(0)
        ->and(Penerimaan::count())->toBeGreaterThan(0)
        ->and(TransaksiPenerimaan::count())->toBeGreaterThan(0)
        ->and(TransaksiPenerimaan::whereNotNull('sumber_dana_id')->count())->toBe(TransaksiPenerimaan::count())
        ->and(Pengeluaran::count())->toBeGreaterThan(0)
        ->and(PermintaanDana::count())->toBeGreaterThan(0)
        ->and(TransferDana::count())->toBeGreaterThan(0)
        ->and(TransferDana::whereColumn('sumber_dana_pengirim_id', 'sumber_dana_penerima_id')->count())->toBe(0);
});

test('sample data seeder tidak menggandakan data saat sudah ada', function () {
    Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    // Sejumlah anggaran/riil sudah ada (misal dari impor).
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);
    $program = Program::create(['kode_program' => '1.1', 'nama_program' => 'Program A', 'opd_id' => Opd::first()->id]);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => Opd::first()->id,
        'kode_kegiatan' => '1.1.1', 'nama_kegiatan' => 'Kegiatan A',
        'pagu' => 0, 'realisasi' => 0,
    ]);
    $sub = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id,
        'kode_sub_kegiatan' => '1.1.1.1', 'nama_sub_kegiatan' => 'Sub A',
        'pagu' => 0, 'realisasi' => 0,
    ]);
    Belanja::create([
        'sub_kegiatan_id' => $sub->id, 'rekening_id' => $rekening->id,
        'opd_id' => Opd::first()->id, 'pagu' => 1000000, 'realisasi' => 0,
    ]);

    $this->artisan('db:seed', ['--class' => SampleDataSeeder::class])->assertExitCode(0);

    // Seeder berhenti lebih awal; tidak ada penerimaan contoh tambahan.
    expect(Penerimaan::count())->toBe(0);
});
