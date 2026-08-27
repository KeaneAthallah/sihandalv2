<?php

namespace App\Console\Commands;

use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SumberDana;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSumberDana extends Command
{
    protected $signature = 'app:import-sumber-dana {--path=plan/sumberdana26.csv}';

    protected $description = 'Import sumber dana data from CSV file';

    public function handle(): int
    {
        $path = base_path($this->option('path'));

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return 1;
        }

        $this->info('Importing sumber dana data...');

        $row = 0;

        DB::transaction(function () use ($path, &$row) {
            $handle = fopen($path, 'r');
            $header = fgetcsv($handle);

            $opdCache = [];
            $rekeningCache = [];

            while (($data = fgetcsv($handle)) !== false) {
                $row++;

                $kodeSkpd = trim($data[1] ?? '');
                $namaSkpd = trim($data[2] ?? '');
                $kodeSubUnit = trim($data[3] ?? '');
                $namaSubUnit = trim($data[4] ?? '');
                $kodeKegiatan = trim($data[5] ?? '');
                $namaKegiatan = trim($data[6] ?? '');
                $kodeSubKegiatan = trim($data[7] ?? '');
                $namaSubKegiatan = trim($data[8] ?? '');
                $kodeRek = trim($data[9] ?? '');
                $namaRek = trim($data[10] ?? '');
                $pagu = (float) str_replace([',', ' '], '', $data[11] ?? '0');
                $sumberDana = trim($data[12] ?? '');

                if (empty($kodeSkpd)) {
                    continue;
                }

                // Create or get OPD
                if (! isset($opdCache[$kodeSkpd])) {
                    $opd = Opd::firstOrCreate(
                        ['kode' => $kodeSkpd],
                        [
                            'nama' => $namaSkpd,
                            'kode_sub_unit' => $kodeSubUnit,
                            'nama_sub_unit' => $namaSubUnit,
                        ]
                    );
                    $opdCache[$kodeSkpd] = $opd;
                }
                $opd = $opdCache[$kodeSkpd];

                // Create or get Rekening
                if (! empty($kodeRek) && ! isset($rekeningCache[$kodeRek])) {
                    $tipe = 'kas';
                    if (str_starts_with($kodeRek, '4')) {
                        $tipe = 'pendapatan';
                    } elseif (str_starts_with($kodeRek, '5')) {
                        $tipe = 'belanja';
                    } elseif (str_starts_with($kodeRek, '1')) {
                        $tipe = 'kas';
                    }

                    $rekening = Rekening::firstOrCreate(
                        ['kode' => $kodeRek],
                        [
                            'nama' => $namaRek,
                            'tipe' => $tipe,
                            'saldo' => 0,
                        ]
                    );
                    $rekeningCache[$kodeRek] = $rekening;
                }

                // Create global SumberDana record by name
                $sumber = SumberDana::firstOrCreate(['nama_sumber_dana' => $sumberDana]);

                // Derive Program from the kegiatan code (first two segments) and get/create it
                $segments = explode('.', $kodeKegiatan);
                $kodeProgram = count($segments) >= 2 ? implode('.', array_slice($segments, 0, 2)) : $kodeKegiatan;
                $program = Program::firstOrCreate(
                    ['kode_program' => $kodeProgram],
                    ['nama_program' => 'Program '.$kodeProgram]
                );

                // Create Kegiatan record
                Kegiatan::create([
                    'program_id' => $program->id,
                    'opd_id' => $opd->id,
                    'sumber_dana_id' => $sumber->id,
                    'kode_kegiatan' => $kodeKegiatan,
                    'nama_kegiatan' => $namaKegiatan,
                    'kode_sub_kegiatan' => $kodeSubKegiatan,
                    'nama_sub_kegiatan' => $namaSubKegiatan,
                    'kode_rekening' => $kodeRek,
                    'nama_rekening' => $namaRek,
                    'pagu' => $pagu,
                    'realisasi' => 0,
                    'persentase' => 0,
                ]);

                // Update OPD total pagu
                $opd->increment('total_pagu', $pagu);
            }

            fclose($handle);
        });

        $this->info("Imported {$row} rows successfully.");

        return 0;
    }
}
