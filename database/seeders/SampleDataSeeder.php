<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\Program;
use App\Models\SumberDana;
use App\Models\TransferDana;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $opds = Opd::all();

        if ($opds->isEmpty()) {
            return;
        }

        // Sample Sumber Dana (global)
        foreach (['Dana Alokasi Umum (DAU)', 'Dana Alokasi Khusus (DAK)', 'Pendapatan Asli Daerah (PAD)'] as $nama) {
            SumberDana::firstOrCreate(['nama_sumber_dana' => $nama]);
        }
        $sumberDana = SumberDana::first();

        // Sample Program & Kegiatan
        $samplePrograms = [
            ['kode_program' => '1.1', 'nama_program' => 'Program Penunjang Urusan Pemerintahan Daerah'],
            ['kode_program' => '2.1', 'nama_program' => 'Program Peningkatan Sarana dan Prasarana'],
            ['kode_program' => '3.1', 'nama_program' => 'Program Pemberdayaan Masyarakat'],
        ];

        foreach ($opds->take(5) as $idx => $opd) {
            $program = $samplePrograms[$idx % count($samplePrograms)];
            $program = Program::firstOrCreate([
                'kode_program' => $program['kode_program'],
            ], [
                'nama_program' => $program['nama_program'],
            ]);

            $pagu = rand(100000000, 1000000000);
            $realisasi = rand(50000000, 500000000);

            Kegiatan::create([
                'program_id' => $program->id,
                'opd_id' => $opd->id,
                'sumber_dana_id' => $sumberDana?->id,
                'kode_kegiatan' => $program->kode_program.'.'.rand(1, 9),
                'nama_kegiatan' => 'Penyelenggaraan Kegiatan '.($idx + 1),
                'kode_sub_kegiatan' => $program->kode_program.'.1',
                'nama_sub_kegiatan' => 'Pelaksanaan Kegiatan '.($idx + 1),
                'kode_rekening' => '5.2.'.rand(1, 9),
                'nama_rekening' => 'Belanja Operasional',
                'pagu' => $pagu,
                'realisasi' => $realisasi,
                'persentase' => $pagu > 0 ? round($realisasi / $pagu * 100, 2) : 0,
            ]);
        }

        // Sample Penerimaan data
        foreach ($opds->take(5) as $opd) {
            foreach (['DBH', 'PAD', 'DAU'] as $sumberDana) {
                Penerimaan::create([
                    'opd_id' => $opd->id,
                    'kode_sumber_dana' => $sumberDana,
                    'nama_sumber_dana' => $sumberDana,
                    'target' => rand(50000000, 500000000),
                    'realisasi' => rand(10000000, 300000000),
                    'persentase' => rand(20, 90),
                    'tanggal' => now()->subDays(rand(1, 30)),
                    'keterangan' => "Penerimaan {$sumberDana} untuk {$opd->nama}",
                ]);
            }
        }

        // Sample Pengeluaran data
        foreach ($opds->take(5) as $opd) {
            foreach (['Belanja Pegawai', 'Belanja Barang', 'Belanja Modal'] as $kegiatan) {
                Pengeluaran::create([
                    'opd_id' => $opd->id,
                    'kode_kegiatan' => '5.1.'.rand(1, 9),
                    'nama_kegiatan' => $kegiatan,
                    'sumber_dana' => 'DAU',
                    'anggaran' => rand(100000000, 1000000000),
                    'realisasi' => rand(50000000, 500000000),
                    'persentase' => rand(30, 85),
                    'tanggal' => now()->subDays(rand(1, 30)),
                    'keterangan' => "Pengeluaran {$kegiatan}",
                ]);
            }
        }

        // Sample Permintaan Dana
        foreach ($opds->take(3) as $index => $opd) {
            $statuses = ['draft', 'menunggu', 'disetujui', 'ditolak'];
            PermintaanDana::create([
                'nomor_permintaan' => 'PD-'.str_pad($index + 1, 4, '0', STR_PAD_LEFT).'/'.now()->format('Y'),
                'opd_id' => $opd->id,
                'jumlah' => rand(50000000, 500000000),
                'sumber_dana' => 'DAU',
                'keperluan' => 'Pembiayaan operasional bulanan',
                'status' => $statuses[array_rand($statuses)],
                'tanggal' => now()->subDays(rand(1, 15)),
            ]);
        }

        // Sample Transfer Dana
        foreach ($opds->take(3) as $index => $opd) {
            TransferDana::create([
                'nomor_transfer' => 'TF-'.str_pad($index + 1, 4, '0', STR_PAD_LEFT).'/'.now()->format('Y'),
                'opd_id' => $opd->id,
                'jumlah' => rand(100000000, 1000000000),
                'sumber_dana' => 'DBH',
                'keterangan' => 'Transfer dana operasional',
                'status' => 'selesai',
                'tanggal' => now()->subDays(rand(1, 10)),
                'tanggal_selesai' => now()->subDays(rand(1, 5)),
            ]);
        }
    }
}
