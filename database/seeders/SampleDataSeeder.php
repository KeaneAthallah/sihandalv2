<?php

namespace Database\Seeders;

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
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
