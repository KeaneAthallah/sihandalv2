<?php

namespace App\Console\Commands;

use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSumberDana extends Command
{
    protected $signature = 'app:import-sumber-dana {csv? : Path to the sumber dana CSV file} {--path=plan/sumberdana26.csv}';

    protected $description = 'Import 2026 budget (program/kegiatan/rekening/sumber dana) data from a CSV file into the Sihandal database. Safe to re-run; never doubles budget.';

    private const REQUIRED_HEADERS = [
        'KDSKPD', 'NMSKPD', 'KDSUBUNIT', 'NMSUBUNIT',
        'KDKEGIATAN', 'NMKEGIATAN', 'KDSUBKEGIATAN', 'NMSUBKEGIATAN',
        'KDREK', 'NMREK', 'PAGU', 'SUMBERDANA',
    ];

    public function handle(): int
    {
        $csvPath = $this->argument('csv') ?? $this->option('path');
        $path = base_path($csvPath);

        if (! file_exists($path)) {
            $this->error("CSV file not found: {$path}");

            return 1;
        }

        if (! $this->validateHeaders($path)) {
            return 1;
        }

        $stats = [
            'rows' => 0,
            'skipped' => 0,
            'opds_created' => 0,
            'opds_updated' => 0,
            'programs_created' => 0,
            'programs_updated' => 0,
            'kegiatan_created' => 0,
            'kegiatan_updated' => 0,
            'rekenings_created' => 0,
            'rekenings_updated' => 0,
            'sumber_created' => 0,
            'sumber_updated' => 0,
            'total_pagu' => 0.0,
        ];

        $this->info("Importing 2026 budget data from: {$path}");

        try {
            DB::transaction(function () use ($path, &$stats) {
                $aggregates = $this->parseCsv($path, $stats);
                $this->writeAggregates($aggregates, $stats);
            });
        } catch (\Throwable $e) {
            $this->error('Import failed and was rolled back. Nothing was written.');
            $this->error($e->getMessage());

            return 1;
        }

        $this->renderReport($stats);

        return 0;
    }

    /**
     * Read, normalize and aggregate the CSV into in-memory structures.
     *
     * @return array{
     *     opds: array<string, array{nama: string, kode_sub_unit: ?string, nama_sub_unit: ?string, pagu: float}>,
     *     rekenings: array<string, array{nama: string, tipe: string}>,
     *     sumber_danas: array<string, true>,
     *     programs: array<string, array{nama_program: string}>,
     *     kegiatans: array<string, array{
     *         opd_kode: string, program_kode: string, sumber_name: ?string,
     *         kode_kegiatan: string, nama_kegiatan: string,
     *         kode_sub_kegiatan: ?string, nama_sub_kegiatan: ?string,
     *         kode_rekening: ?string, nama_rekening: ?string,
     *         pagu: float,
     *     }>
     * }
     */
    private function parseCsv(string $path, array &$stats): array
    {
        $handle = fopen($path, 'r');
        $firstLine = true;

        $opds = [];
        $rekenings = [];
        $sumberDanas = [];
        $programs = [];
        $kegiatans = [];

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if ($firstLine) {
                $firstLine = false;

                continue; // header already validated
            }

            $kodeSkpd = $this->clean($row[1] ?? null);
            $namaSkpd = $this->clean($row[2] ?? null);
            $kodeSubUnit = $this->clean($row[3] ?? null);
            $namaSubUnit = $this->clean($row[4] ?? null);
            $kodeKegiatan = $this->clean($row[5] ?? null);
            $namaKegiatan = $this->clean($row[6] ?? null);
            $kodeSubKegiatan = $this->clean($row[7] ?? null);
            $namaSubKegiatan = $this->clean($row[8] ?? null);
            $kodeRek = $this->clean($row[9] ?? null);
            $namaRek = $this->clean($row[10] ?? null);
            $pagu = $this->normalizePagu($row[11] ?? null);
            $sumberName = $this->clean($row[12] ?? null);

            $stats['rows']++;

            $valid = $kodeSkpd !== null
                && $kodeKegiatan !== null
                && $namaKegiatan !== null
                && $pagu > 0;

            if (! $valid) {
                $stats['skipped']++;

                continue;
            }

            // OPD keyed by KDSKPD (unique opds.kode). First-seen sub-unit is kept.
            if (! isset($opds[$kodeSkpd])) {
                $opds[$kodeSkpd] = [
                    'nama' => $namaSkpd ?? $kodeSkpd,
                    'kode_sub_unit' => $kodeSubUnit ?: null,
                    'nama_sub_unit' => $namaSubUnit ?: null,
                    'pagu' => 0.0,
                ];
            }
            $opds[$kodeSkpd]['pagu'] += $pagu;

            if ($kodeRek !== null) {
                $rekenings[$kodeRek] = [
                    'nama' => $namaRek ?? $kodeRek,
                    'tipe' => $this->rekeningTipe($kodeRek),
                ];
            }

            if ($sumberName !== null) {
                $sumberDanas[$sumberName] = true;
            }

            $programKode = $this->kodeProgram($kodeKegiatan);
            $programs[$programKode] = ['nama_program' => "Program {$programKode}"];

            $kegiatanKey = implode('|', [
                $kodeSkpd,
                $kodeKegiatan,
                $kodeSubKegiatan ?? '',
                $sumberName ?? '',
            ]);

            if (! isset($kegiatans[$kegiatanKey])) {
                $kegiatans[$kegiatanKey] = [
                    'opd_kode' => $kodeSkpd,
                    'program_kode' => $programKode,
                    'sumber_name' => $sumberName,
                    'kode_kegiatan' => $kodeKegiatan,
                    'nama_kegiatan' => $namaKegiatan,
                    'kode_sub_kegiatan' => $kodeSubKegiatan,
                    'nama_sub_kegiatan' => $namaSubKegiatan,
                    'kode_rekening' => $kodeRek,
                    'nama_rekening' => $namaRek,
                    'pagu' => 0.0,
                ];
            }
            $kegiatans[$kegiatanKey]['pagu'] += $pagu;
        }

        fclose($handle);

        return [
            'opds' => $opds,
            'rekenings' => $rekenings,
            'sumber_danas' => $sumberDanas,
            'programs' => $programs,
            'kegiatans' => $kegiatans,
        ];
    }

    /**
     * Persist the aggregated structures using deterministic update-or-create keys.
     */
    private function writeAggregates(array $aggregates, array &$stats): void
    {
        $opdIdByKode = [];
        $sumberIdByName = [];

        // OPDs
        foreach ($aggregates['opds'] as $kode => $data) {
            $opd = Opd::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $data['nama'], 'total_pagu' => $data['pagu']]
            );

            if ($opd->wasRecentlyCreated) {
                $stats['opds_created']++;
            } else {
                $stats['opds_updated']++;
            }

            // Only fill sub-unit fields on a brand new OPD to avoid overwriting live data.
            if ($opd->wasRecentlyCreated && $data['kode_sub_unit']) {
                $opd->update([
                    'kode_sub_unit' => $data['kode_sub_unit'],
                    'nama_sub_unit' => $data['nama_sub_unit'],
                ]);
            }

            $opdIdByKode[$kode] = $opd->id;
        }

        // Rekenings (master list keyed by kode)
        foreach ($aggregates['rekenings'] as $kode => $data) {
            $rekening = Rekening::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $data['nama'], 'tipe' => $data['tipe'], 'saldo' => 0]
            );

            $rekening->wasRecentlyCreated ? $stats['rekenings_created']++ : $stats['rekenings_updated']++;
        }

        // Sumber Dana (global master list keyed by name)
        foreach ($aggregates['sumber_danas'] as $nama => $_) {
            $sumber = SumberDana::updateOrCreate(['nama_sumber_dana' => $nama]);
            $sumber->wasRecentlyCreated ? $stats['sumber_created']++ : $stats['sumber_updated']++;
            $sumberIdByName[$nama] = $sumber->id;
        }

        // Programs (global master list keyed by kode_program)
        foreach ($aggregates['programs'] as $kode => $data) {
            $program = Program::updateOrCreate(
                ['kode_program' => $kode],
                ['nama_program' => $data['nama_program']]
            );
            $program->wasRecentlyCreated ? $stats['programs_created']++ : $stats['programs_updated']++;
        }

        // Kegiatan (budget holder, keyed by opd + kegiatan + sub-kegiatan + sumber dana)
        foreach ($aggregates['kegiatans'] as $key => $data) {
            $opdId = $opdIdByKode[$data['opd_kode']];
            $sumberId = $data['sumber_name'] !== null ? $sumberIdByName[$data['sumber_name']] : null;
            $programKode = $data['program_kode'];
            $programId = Program::where('kode_program', $programKode)->value('id');

            $kegiatan = Kegiatan::updateOrCreate(
                [
                    'opd_id' => $opdId,
                    'kode_kegiatan' => $data['kode_kegiatan'],
                    'kode_sub_kegiatan' => $data['kode_sub_kegiatan'],
                    'sumber_dana_id' => $sumberId,
                ],
                [
                    'program_id' => $programId,
                    'nama_kegiatan' => $data['nama_kegiatan'],
                    'nama_sub_kegiatan' => $data['nama_sub_kegiatan'],
                    'kode_rekening' => $data['kode_rekening'],
                    'nama_rekening' => $data['nama_rekening'],
                    'pagu' => $data['pagu'],
                    'realisasi' => 0,
                    'persentase' => 0,
                ]
            );

            $kegiatan->wasRecentlyCreated ? $stats['kegiatan_created']++ : $stats['kegiatan_updated']++;
            $stats['total_pagu'] += $data['pagu'];
        }

        // Recompute OPD totals as the exact sum of its kegiatan pagu (idempotent).
        foreach ($aggregates['opds'] as $kode => $data) {
            $opdId = $opdIdByKode[$kode];
            $sum = Kegiatan::where('opd_id', $opdId)->sum('pagu');
            Opd::where('id', $opdId)->update(['total_pagu' => $sum]);
        }

        $this->ensureTahunAnggaran();
    }

    private function ensureTahunAnggaran(): void
    {
        $existing = TahunAnggaran::where('tahun', '2026')->first();

        if ($existing === null) {
            TahunAnggaran::create([
                'tahun' => '2026',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'status' => 'open',
                'is_active' => true,
            ]);
            $this->line('  -> created fiscal year 2026');

            return;
        }

        if ($existing->status === 'open' && $existing->is_active) {
            return;
        }

        $existing->update(['status' => 'open', 'is_active' => true]);
        $this->line('  -> ensured 2026 fiscal year is open + active');
    }

    private function validateHeaders(string $path): bool
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, ',', '"', '\\');
        fclose($handle);

        if ($header === false) {
            $this->error('Could not read the CSV header.');

            return false;
        }

        $header = array_map(fn ($h) => $this->clean($h), $header);

        foreach (self::REQUIRED_HEADERS as $required) {
            if (! in_array($required, $header, true)) {
                $this->error("Missing required column header: {$required}");
                $this->error('Found headers: '.implode(', ', $header));

                return false;
            }
        }

        return true;
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace(["\xEF\xBB\xBF", "\u{A0}", "\u{FEFF}"], '', (string) $value);
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalizePagu(mixed $value): float
    {
        if ($value === null) {
            return 0.0;
        }

        $value = preg_replace('/[\s\xC2\xA0]/u', '', (string) $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    private function rekeningTipe(string $kode): string
    {
        if (str_starts_with($kode, '4')) {
            return 'pendapatan';
        }

        if (str_starts_with($kode, '5')) {
            return 'belanja';
        }

        if (str_starts_with($kode, '1')) {
            return 'kas';
        }

        return 'kas';
    }

    private function kodeProgram(string $kodeKegiatan): string
    {
        $segments = explode('.', $kodeKegiatan);

        if (count($segments) >= 3) {
            return implode('.', array_slice($segments, 0, 3));
        }

        return $kodeKegiatan;
    }

    private function renderReport(array $stats): void
    {
        $this->newLine();
        $this->info('=== Import summary (2026) ===');
        $this->table(
            ['Item', 'Count'],
            [
                ['CSV rows processed', number_format($stats['rows'])],
                ['Rows skipped (invalid/missing)', number_format($stats['skipped'])],
                ['OPDs created', number_format($stats['opds_created'])],
                ['OPDs updated', number_format($stats['opds_updated'])],
                ['Programs created', number_format($stats['programs_created'])],
                ['Programs updated', number_format($stats['programs_updated'])],
                ['Kegiatan created', number_format($stats['kegiatan_created'])],
                ['Kegiatan updated', number_format($stats['kegiatan_updated'])],
                ['Rekenings created', number_format($stats['rekenings_created'])],
                ['Rekenings updated', number_format($stats['rekenings_updated'])],
                ['Sumber dana created', number_format($stats['sumber_created'])],
                ['Sumber dana updated', number_format($stats['sumber_updated'])],
                ['Total imported pagu', 'Rp '.number_format($stats['total_pagu'], 2, ',', '.')],
            ]
        );
    }
}
