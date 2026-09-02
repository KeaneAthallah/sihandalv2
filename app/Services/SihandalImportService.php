<?php

namespace App\Services;

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\TransaksiPenerimaan;
use App\Support\CsvReader;
use App\Support\XlsxReader;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates the Sihandal data import.
 *
 * Budget (sumberdana CSV) lands at the Belanja leaf of the hierarchy
 * Opd -> Program -> Kegiatan -> SubKegiatan -> Belanja (Rekening +
 * SumberDana).
 *
 * Revenue (PENERIMAAN XLSX, province-wide with no per-OPD owner) lands on
 * TransaksiPenerimaan records, one per workbook row, grouped under a master
 * Penerimaan per Sumber Dana (opd_id = null). A master's realization is the
 * SUM of its transactions, so importing never writes realization onto the
 * master directly.
 *
 * Parent aggregates (SubKegiatan.pagu, Kegiatan.pagu, Opd.total_pagu) are
 * always re-derived from their leaf children so no pagu is double counted,
 * and persisted so existing views keep working.
 *
 * The whole run is wrapped in a single transaction and is idempotent: rows
 * already present (matched by their source traceability key) are skipped.
 */
class SihandalImportService
{
    private const DATA_DIR = 'database/seeders/data';

    /** @var array<string, Opd> */
    private array $opds = [];

    /** @var array<string, Program> */
    private array $programs = [];

    /** @var array<string, Rekening> */
    private array $rekenings = [];

    /** @var array<string, SumberDana> */
    private array $sumberDanas = [];

    /** @var array<string, Kegiatan> */
    private array $kegiatans = [];

    /** @var array<string, SubKegiatan> */
    private array $subKegiatans = [];

    private ?TahunAnggaran $tahunAnggaran = null;

    private ?string $budgetFile = null;

    private ?string $revenueFile = null;

    /** @var list<array{file: string, row: int|string, reason: string}> */
    private array $failures = [];

    /** @var array<string, bool> */
    private array $existingBelanjaKeys = [];

    /** @var array<string, bool> */
    private array $existingPenerimaanKeys = [];

    /** @var array<string, Penerimaan> */
    private array $revenueMasters = [];

    public function import(string $csvPath, string $xlsxPath, bool $fresh = false, bool $dryRun = false): array
    {
        $this->budgetFile = basename($csvPath);
        $this->revenueFile = basename($xlsxPath);

        DB::beginTransaction();

        try {
            if ($fresh) {
                $this->wipe();
            }

            $this->loadExistingKeys();
            $this->ensureTahunAnggaran();

            $csv = $this->importBudgetCsv($csvPath);
            $xlsx = $this->importRevenueXlsx($xlsxPath);

            $rollups = $this->rebuildRollups();

            $report = $this->buildFinalReport([
                'csv' => $csv,
                'xlsx' => $xlsx,
                'rollups' => $rollups,
                'failureCount' => count($this->failures),
                'failures' => $this->failures,
            ]);

            if ($dryRun) {
                DB::rollBack();

                return $report;
            }

            DB::commit();

            return $report;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    private function importBudgetCsv(string $csvPath): array
    {
        $reader = new CsvReader($csvPath);
        $reader->open();

        $processed = 0;
        $created = 0;
        $skipped = 0;
        $row = 1; // physical line counter (header is line 1)

        while (($record = $reader->read()) !== null) {
            $row++;

            $processed++;

            $no = $record['NO'];
            $key = $this->belanjaKey($no);
            if (isset($this->existingBelanjaKeys[$key])) {
                $skipped++;

                continue;
            }

            try {
                $this->createBelanja($record, $row);
                $created++;
            } catch (\Throwable $e) {
                $this->failures[] = ['file' => $this->budgetFile, 'row' => $row, 'reason' => $e->getMessage()];
            }
        }

        $reader->close();

        return [
            'file' => $this->budgetFile,
            'processed' => $processed,
            'created' => $created,
            'skipped' => $skipped,
            'totalPaguRaw' => $this->sumCsvPagu($csvPath),
        ];
    }

    private function createBelanja(array $record, int $row): void
    {
        $required = ['KDSKPD', 'KDKEGIATAN', 'NMKEGIATAN', 'KDSUBKEGIATAN', 'NMSUBKEGIATAN', 'KDREK', 'NMREK', 'SUMBERDANA'];
        foreach ($required as $field) {
            if ((string) ($record[$field] ?? '') === '' && $field !== 'SUMBERDANA') {
                throw new \RuntimeException("Missing required field '{$field}'");
            }
        }

        $opd = $this->resolveOpd($record);
        $program = $this->resolveProgram($record);
        $rekening = $this->resolveRekening($record);
        $sumberDana = $this->resolveSumberDana($record['SUMBERDANA']);
        $kegiatan = $this->resolveKegiatan($opd, $program, $record, $sumberDana);

        $subKegiatan = $this->resolveSubKegiatan($kegiatan, $record);

        Belanja::create([
            'sub_kegiatan_id' => $subKegiatan->id,
            'rekening_id' => $rekening->id,
            'sumber_dana_id' => $sumberDana->id,
            'opd_id' => $opd->id,
            'pagu' => $this->round2($record['PAGU']),
            'realisasi' => 0,
            'dana_di_commit' => 0,
            'tahun_anggaran_id' => $this->tahunAnggaran->id,
            'source_file' => $this->budgetFile,
            'source_row' => (int) $record['NO'],
            'source_identifier' => (string) $record['NO'],
        ]);
    }

    private function importRevenueXlsx(string $xlsxPath): array
    {
        $reader = new XlsxReader($xlsxPath);
        $rows = $reader->readSheet('Data Input');

        $created = 0;
        $skipped = 0;
        $processedRows = 0;
        $amountCredited = 0.0;
        $mastersCreated = 0;

        foreach ($rows as $rowNumber => $cells) {
            if ($rowNumber < 8 || ! array_key_exists(7, $cells)) {
                continue;
            }

            $processedRows++;

            $key = $this->penerimaanKey($rowNumber);
            if (isset($this->existingPenerimaanKeys[$key])) {
                $skipped++;

                continue;
            }

            try {
                $this->createPenerimaan($cells, $rowNumber);
                $created++;
                $amountCredited += (float) ($cells[7] ?? 0);
            } catch (\Throwable $e) {
                $this->failures[] = ['file' => $this->revenueFile, 'row' => $rowNumber, 'reason' => $e->getMessage()];
            }
        }

        return [
            'file' => $this->revenueFile,
            'sourceRows' => $processedRows,
            'created' => $created,
            'mastersCreated' => count($this->revenueMasters),
            'skipped' => $skipped,
            'expectedSum' => $this->sumXlsxH($rows),
            'creditedSum' => $amountCredited,
        ];
    }

    private function createPenerimaan(array $cells, int $rowNumber): void
    {
        $sumberName = (string) ($cells[9] ?? ($cells[11] ?? ''));
        $sumberDana = $sumberName !== '' ? $this->resolveSumberDana($sumberName) : null;

        $tanggal = isset($cells[3]) && is_string($cells[3]) ? $cells[3] : null;
        $keterangan = (string) ($cells[6] ?? '');

        $amount = (float) ($cells[7] ?? 0);

        $master = $this->resolveRevenueMaster($sumberName, $sumberDana?->id, (string) ($cells[2] ?? null));

        TransaksiPenerimaan::create([
            'penerimaan_id' => $master->id,
            'realisasi' => $amount,
            'tanggal' => $tanggal,
            'keterangan' => $keterangan,
            'source_file' => $this->revenueFile,
            'source_row' => $rowNumber,
            'source_identifier' => "row {$rowNumber}",
        ]);
    }

    /**
     * Resolve (or create) the province-wide master Penerimaan that groups
     * transactions under a given Sumber Dana. Imported revenue masters are
     * keyed by (source_file, nama_sumber_dana) so re-runs are idempotent.
     */
    private function resolveRevenueMaster(string $sumberName, ?int $sumberDanaId, string $kodeSumberDana): Penerimaan
    {
        $key = $sumberName;
        if (isset($this->revenueMasters[$key])) {
            return $this->revenueMasters[$key];
        }

        $name = $sumberName !== '' ? $sumberName : ($kodeSumberDana !== '' ? $kodeSumberDana : 'Penerimaan');

        $master = Penerimaan::firstOrCreate(
            ['source_file' => $this->revenueFile, 'nama_sumber_dana' => $name],
            [
                'opd_id' => null,
                'rekening_id' => null,
                'sumber_dana_id' => $sumberDanaId,
                'tahun_anggaran_id' => $this->tahunAnggaran->id,
                'kode_sumber_dana' => $kodeSumberDana,
                'nama_sumber_dana' => $name,
                'target' => 0,
                'source_file' => $this->revenueFile,
                'source_identifier' => $name,
            ]
        );

        return $this->revenueMasters[$key] = $master;
    }

    private function resolveOpd(array $record): Opd
    {
        $kode = $record['KDSKPD'];
        if (isset($this->opds[$kode])) {
            return $this->opds[$kode];
        }

        $opd = Opd::firstOrCreate(
            ['kode' => $kode],
            [
                'kode' => $kode,
                'nama' => $record['NMSKPD'],
                'nmskpd' => $record['NMSKPD'],
                'kode_sub_unit' => $record['KDSUBUNIT'],
                'nama_sub_unit' => $record['NMSUBUNIT'],
                'total_pagu' => 0,
                'source_file' => $this->budgetFile,
                'source_identifier' => (string) $record['KDSKPD'],
            ]
        );

        return $this->opds[$kode] = $opd;
    }

    private function resolveProgram(array $record): Program
    {
        $kode = $this->programKode($record['KDKEGIATAN']);
        if (isset($this->programs[$kode])) {
            return $this->programs[$kode];
        }

        $program = Program::firstOrCreate(
            ['kode_program' => $kode],
            [
                'kode_program' => $kode,
                'nama_program' => "Program {$kode}",
                'source_file' => $this->budgetFile,
                'source_identifier' => (string) $kode,
            ]
        );

        return $this->programs[$kode] = $program;
    }

    private function resolveRekening(array $record): Rekening
    {
        $kode = $record['KDREK'];
        if (isset($this->rekenings[$kode])) {
            return $this->rekenings[$kode];
        }

        $rekening = Rekening::firstOrCreate(
            ['kode' => $kode],
            [
                'kode' => $kode,
                'nama' => $record['NMREK'],
                'tipe' => $this->rekeningTipe($kode),
                'saldo' => 0,
                'source_file' => $this->budgetFile,
                'source_identifier' => (string) $kode,
            ]
        );

        return $this->rekenings[$kode] = $rekening;
    }

    private function resolveSumberDana(?string $name): SumberDana
    {
        $name = (string) $name;
        if (isset($this->sumberDanas[$name])) {
            return $this->sumberDanas[$name];
        }

        $sumberDana = SumberDana::firstOrCreate(
            ['nama_sumber_dana' => $name],
            ['nama_sumber_dana' => $name,
                'source_file' => basename((string) ($this->budgetFile ?? $this->revenueFile)),
                'source_identifier' => $name,
            ]
        );

        return $this->sumberDanas[$name] = $sumberDana;
    }

    private function resolveKegiatan(Opd $opd, Program $program, array $record, ?SumberDana $sumberDana): Kegiatan
    {
        $kodeKegiatan = $record['KDKEGIATAN'];
        $key = $opd->id.'|'.$kodeKegiatan.'|'.$this->tahunAnggaran->id;
        if (isset($this->kegiatans[$key])) {
            return $this->kegiatans[$key];
        }

        $kegiatan = Kegiatan::firstOrCreate(
            [
                'opd_id' => $opd->id,
                'kode_kegiatan' => $kodeKegiatan,
                'tahun_anggaran_id' => $this->tahunAnggaran->id,
            ],
            [
                'program_id' => $program->id,
                'opd_id' => $opd->id,
                'sumber_dana_id' => $sumberDana?->id,
                'kode_kegiatan' => $kodeKegiatan,
                'nama_kegiatan' => $record['NMKEGIATAN'],
                'tahun_anggaran_id' => $this->tahunAnggaran->id,
                'pagu' => 0,
                'source_file' => $this->budgetFile,
                'source_identifier' => (string) $kodeKegiatan,
            ]
        );

        return $this->kegiatans[$key] = $kegiatan;
    }

    private function resolveSubKegiatan(Kegiatan $kegiatan, array $record): SubKegiatan
    {
        $kodeSub = $record['KDSUBKEGIATAN'];
        $key = $kegiatan->id.'|'.$kodeSub;
        if (isset($this->subKegiatans[$key])) {
            return $this->subKegiatans[$key];
        }

        $subKegiatan = SubKegiatan::firstOrCreate(
            [
                'kegiatan_id' => $kegiatan->id,
                'kode_sub_kegiatan' => $kodeSub,
            ],
            [
                'kegiatan_id' => $kegiatan->id,
                'kode_sub_kegiatan' => $kodeSub,
                'nama_sub_kegiatan' => $record['NMSUBKEGIATAN'],
                'pagu' => 0,
                'source_file' => $this->budgetFile,
                'source_identifier' => (string) $kodeSub,
            ]
        );

        return $this->subKegiatans[$key] = $subKegiatan;
    }

    private function rebuildRollups(): array
    {
        $subPagu = collect(DB::table('belanjas')
            ->select('sub_kegiatan_id', DB::raw('SUM(pagu) as total'))
            ->groupBy('sub_kegiatan_id')
            ->get());

        foreach ($subPagu as $row) {
            SubKegiatan::whereKey($row->sub_kegiatan_id)->update(['pagu' => $row->total]);
        }

        $kegiatanPagu = collect(DB::table('belanjas')
            ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'belanjas.sub_kegiatan_id')
            ->select('sub_kegiatans.kegiatan_id', DB::raw('SUM(belanjas.pagu) as total'))
            ->groupBy('sub_kegiatans.kegiatan_id')
            ->get());

        foreach ($kegiatanPagu as $row) {
            Kegiatan::whereKey($row->kegiatan_id)->update(['pagu' => $row->total]);
        }

        $opdPagu = collect(DB::table('belanjas')
            ->select('opd_id', DB::raw('SUM(pagu) as total'))
            ->groupBy('opd_id')
            ->get());

        foreach ($opdPagu as $row) {
            Opd::whereKey($row->opd_id)->update(['total_pagu' => $row->total]);
        }

        return [
            'subKegiatanUpdated' => $subPagu->count(),
            'kegiatanUpdated' => $kegiatanPagu->count(),
            'opdUpdated' => $opdPagu->count(),
        ];
    }

    private function ensureTahunAnggaran(): void
    {
        $this->tahunAnggaran = TahunAnggaran::firstOrCreate(
            ['tahun' => '2026'],
            [
                'tahun' => '2026',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'status' => 'open',
                'is_active' => true,
            ]
        );
    }

    private function loadExistingKeys(): void
    {
        foreach (Belanja::select(['source_file', 'source_row'])->whereNotNull('source_row')->cursor() as $b) {
            $this->existingBelanjaKeys[$b->source_file.'|'.$b->source_row] = true;
        }

        foreach (TransaksiPenerimaan::select(['source_file', 'source_row'])->whereNotNull('source_row')->cursor() as $t) {
            $this->existingPenerimaanKeys[$t->source_file.'|'.$t->source_row] = true;
        }
    }

    private function wipe(): void
    {
        foreach (['belanjas', 'transaksi_penerimaans', 'penerimaans', 'sub_kegiatans', 'kegiatan', 'programs', 'rekenings', 'sumber_danas', 'tahun_anggarans', 'opds'] as $table) {
            DB::table($table)->delete();
        }

        // Re-seed in-memory maps after a wipe.
        $this->opds = [];
        $this->programs = [];
        $this->rekenings = [];
        $this->sumberDanas = [];
        $this->kegiatans = [];
        $this->subKegiatans = [];
        $this->existingBelanjaKeys = [];
        $this->existingPenerimaanKeys = [];
        $this->revenueMasters = [];
        $this->failures = [];
    }

    private function belanjaKey(int $no): string
    {
        return $this->budgetFile.'|'.$no;
    }

    private function penerimaanKey(int $row): string
    {
        return $this->revenueFile.'|'.$row;
    }

    private function programKode(string $kodeKegiatan): string
    {
        $parts = explode('.', $kodeKegiatan);

        return implode('.', array_slice($parts, 0, 3));
    }

    private function round2(mixed $value): float
    {
        return round($this->normalizePagu($value), 2);
    }

    private function normalizePagu(mixed $value): float
    {
        if ($value === null || $value === '') {
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

    private function sumCsvPagu(string $csvPath): array
    {
        $reader = new CsvReader($csvPath);
        $reader->open();

        $raw = 0.0;
        $rounded = 0.0;

        while (($record = $reader->read()) !== null) {
            $pagu = $this->normalizePagu($record['PAGU']);
            $raw += $pagu;
            $rounded += round($pagu, 2);
        }

        $reader->close();

        return [
            'raw' => $raw,
            'rounded' => $rounded,
            'diff' => $raw - $rounded,
        ];
    }

    private function sumXlsxH(array $rows): float
    {
        $sum = 0.0;

        foreach ($rows as $number => $cells) {
            if ($number >= 8 && isset($cells[7])) {
                $sum += (float) $cells[7];
            }
        }

        return $sum;
    }

    private function buildFinalReport(array $inner): array
    {
        $belanjaActual = (float) Belanja::where('source_file', $this->budgetFile)->sum('pagu');
        $penerimaanActual = (float) TransaksiPenerimaan::where('source_file', $this->revenueFile)->sum('realisasi');

        $csv = $inner['csv'];
        $xlsx = $inner['xlsx'];

        return [
            'created' => [
                'opd' => Opd::count(),
                'program' => Program::count(),
                'rekening' => Rekening::count(),
                'sumberDana' => SumberDana::count(),
                'tahunAnggaran' => TahunAnggaran::count(),
                'kegiatan' => Kegiatan::count(),
                'subKegiatan' => SubKegiatan::count(),
                'belanja' => Belanja::count(),
                'penerimaan' => Penerimaan::count(),
                'transaksiPenerimaan' => TransaksiPenerimaan::count(),
            ],
            'budget' => [
                'processed' => $csv['processed'],
                'created' => $csv['created'],
                'skipped' => $csv['skipped'],
                'expectedSumRaw' => $csv['totalPaguRaw']['raw'],
                'expectedSumRounded' => $csv['totalPaguRaw']['rounded'],
                'roundingDiff' => $csv['totalPaguRaw']['diff'],
                'actualSum' => $belanjaActual,
                'reconciled' => abs($belanjaActual - $csv['totalPaguRaw']['rounded']) < 0.01,
            ],
            'revenue' => [
                'sourceRows' => $xlsx['sourceRows'],
                'created' => $xlsx['created'],
                'skipped' => $xlsx['skipped'],
                'expectedSum' => $xlsx['expectedSum'],
                'actualSum' => $penerimaanActual,
                'reconciled' => abs($penerimaanActual - $xlsx['expectedSum']) < 0.01,
            ],
            'rollups' => $inner['rollups'],
            'failureCount' => $inner['failureCount'],
            'failures' => $inner['failures'],
        ];
    }
}
