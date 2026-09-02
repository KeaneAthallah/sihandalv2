<?php

namespace Database\Seeders;

use App\Models\Belanja;
use App\Models\Dinas;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\TransferDana;
use App\Models\Unit;
use App\Models\Upt;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $opds = Opd::all();

        if ($opds->isEmpty()) {
            return;
        }

        // Once authoritative budget/revenue data exists (e.g. the real import
        // or a previous sample pass), do not inject duplicate sample rows.
        if (Belanja::exists() || Penerimaan::exists()) {
            return;
        }

        // Sample Sumber Dana (global)
        foreach (['Dana Alokasi Umum (DAU)', 'Dana Alokasi Khusus (DAK)', 'Pendapatan Asli Daerah (PAD)'] as $nama) {
            SumberDana::firstOrCreate(['nama_sumber_dana' => $nama]);
        }
        $sumberDana = SumberDana::first();

        // Sample Rekening (global)
        $rekeningJasa = Rekening::firstOrCreate([
            'kode' => '5.2.1',
        ], [
            'nama' => 'Belanja Jasa', 'tipe' => 'belanja', 'saldo' => 0,
        ]);

        // Reference to a valid active Tahun Anggaran (existing one or first)
        $tahunAnggaran = TahunAnggaran::where('is_active', true)->first()
            ?? TahunAnggaran::first();

        // Sample Program & Kegiatan
        $samplePrograms = [
            ['kode_program' => '1.1', 'nama_program' => 'Program Penunjang Urusan Pemerintahan Daerah'],
            ['kode_program' => '2.1', 'nama_program' => 'Program Peningkatan Sarana dan Prasarana'],
            ['kode_program' => '3.1', 'nama_program' => 'Program Pemberdayaan Masyarakat'],
        ];

        foreach ($opds->take(5) as $idx => $opd) {
            // Hierarchy: OPD -> Dinas -> Unit, plus UPT
            if (! Dinas::where('opd_id', $opd->id)->exists()) {
                Dinas::create(['kode' => 'DIN-'.$opd->kode, 'nama' => 'Dinas '.$opd->nama, 'opd_id' => $opd->id]);
            }
            $dinas = Dinas::where('opd_id', $opd->id)->first();

            if (! Unit::where('opd_id', $opd->id)->exists()) {
                Unit::create(['kode' => 'UNIT-'.$opd->kode, 'nama' => 'Unit '.$opd->nama, 'opd_id' => $opd->id, 'dinas_id' => $dinas->id]);
            }

            if (! Upt::where('opd_id', $opd->id)->exists()) {
                Upt::create(['kode' => 'UPT-'.$opd->kode, 'nama' => 'UPT '.$opd->nama, 'opd_id' => $opd->id]);
            }

            $program = $samplePrograms[$idx % count($samplePrograms)];
            $program = Program::firstOrCreate([
                'kode_program' => $program['kode_program'],
            ], [
                'nama_program' => $program['nama_program'],
                'opd_id' => $opd->id,
                'tahun_anggaran_id' => $tahunAnggaran?->id,
            ]);

            $kegiatan = Kegiatan::create([
                'program_id' => $program->id,
                'opd_id' => $opd->id,
                'sumber_dana_id' => $sumberDana?->id,
                'kode_kegiatan' => $program->kode_program.'.'.($idx + 1),
                'nama_kegiatan' => 'Penyelenggaraan Kegiatan '.($idx + 1),
                'pagu' => 0,
                'realisasi' => 0,
                'persentase' => 0,
            ]);

            // Sub Kegiatan + Belanja (aggregation source)
            $subPagu = rand(100000000, 1000000000);
            $subRealisasi = rand(50000000, 500000000);

            $subKegiatan = SubKegiatan::create([
                'kegiatan_id' => $kegiatan->id,
                'kode_sub_kegiatan' => $kegiatan->kode_kegiatan.'.1',
                'nama_sub_kegiatan' => 'Pelaksanaan Kegiatan '.($idx + 1),
                'pagu' => 0,
                'realisasi' => 0,
                'persentase' => 0,
            ]);

            Belanja::create([
                'sub_kegiatan_id' => $subKegiatan->id,
                'rekening_id' => $rekeningJasa->id,
                'sumber_dana_id' => $sumberDana?->id,
                'opd_id' => $opd->id,
                'pagu' => $subPagu,
                'realisasi' => $subRealisasi,
                'dana_di_commit' => 0,
                'tahun_anggaran_id' => $tahunAnggaran?->id,
            ]);
        }

        // Sample Penerimaan data
        foreach ($opds->take(5) as $opd) {
            foreach (['DBH', 'PAD', 'DAU'] as $sumberDanaNama) {
                Penerimaan::create([
                    'opd_id' => $opd->id,
                    'sumber_dana_id' => $sumberDana?->id,
                    'kode_sumber_dana' => $sumberDanaNama,
                    'nama_sumber_dana' => $sumberDana?->nama_sumber_dana ?? $sumberDanaNama,
                    'target' => rand(50000000, 500000000),
                    'realisasi' => rand(10000000, 300000000),
                    'persentase' => rand(20, 90),
                    'tanggal' => now()->subDays(rand(1, 30)),
                    'keterangan' => "Penerimaan {$sumberDanaNama} untuk {$opd->nama}",
                ]);
            }
        }

        // Sample Pengeluaran data
        foreach ($opds->take(5) as $opd) {
            foreach (['Belanja Pegawai', 'Belanja Barang', 'Belanja Modal'] as $kegiatanNama) {
                Pengeluaran::create([
                    'opd_id' => $opd->id,
                    'sumber_dana_id' => $sumberDana?->id,
                    'kode_kegiatan' => '5.1.'.rand(1, 9),
                    'nama_kegiatan' => $kegiatanNama,
                    'sumber_dana' => $sumberDana?->nama_sumber_dana ?? 'DAU',
                    'anggaran' => rand(100000000, 1000000000),
                    'realisasi' => rand(50000000, 500000000),
                    'persentase' => rand(30, 85),
                    'tanggal' => now()->subDays(rand(1, 30)),
                    'keterangan' => "Pengeluaran {$kegiatanNama}",
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
                'sumber_dana_id' => $sumberDana?->id,
                'sumber_dana' => $sumberDana?->nama_sumber_dana ?? 'DAU',
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
                'sumber_dana' => $sumberDana?->nama_sumber_dana ?? 'DBH',
                'keterangan' => 'Transfer dana operasional',
                'status' => 'selesai',
                'tanggal' => now()->subDays(rand(1, 10)),
                'tanggal_selesai' => now()->subDays(rand(1, 5)),
            ]);
        }
    }
}
