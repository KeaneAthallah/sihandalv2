<?php

namespace App\Services;

use App\Models\LaporanSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Imports BPKAD aggregate PDF report snapshots that have already been
 * converted to JSON (see plan/scripts/parse_pdf.py). The importer works on the
 * normalized JSON only; it never parses PDFs itself.
 *
 * Each line becomes an immutable LaporanSnapshot row carrying its full source
 * traceability. Re-running the same file is idempotent: rows already present,
 * matched by (source_file, source_identifier), are skipped. The whole run is
 * wrapped in a transaction, and --dry-run rolls everything back.
 *
 * These snapshots are stored entirely outside the transactional tables
 * (penerimaans / transaksi_penerimaans / pengeluarans / posisi_kas) so they
 * never duplicate or alter the source transaction data that kas balances are
 * derived from.
 */
class LaporanSnapshotImportService
{
    private const DISK = 'laporan';

    /**
     * @var array<int, true> existing source_row numbers for this file
     */
    private array $existingRows = [];

    /**
     * @var array<string, array{processed: int, created: int, skipped: int, expected: float, imported: float, reconciled: bool}>
     */
    private array $report = [];

    /**
     * @var list<array{file: string, index: int|string, reason: string}>
     */
    private array $failures = [];

    /**
     * @param  string  $filename  JSON filename relative to the laporan disk
     */
    public function import(string $filename, bool $dryRun = false): array
    {
        $this->failures = [];

        $json = $this->readJson($filename);

        DB::beginTransaction();

        try {
            $report = $this->processFile($json, $filename);

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

    /**
     * Import every .json snapshot on the laporan disk in a single transaction.
     * If any file fails, the whole batch is rolled back so a bad file can never
     * leave an earlier file partially committed.
     */
    public function importDir(bool $dryRun = false): array
    {
        $files = $this->jsonFiles();
        $this->failures = [];
        $combined = [];

        DB::beginTransaction();

        try {
            foreach ($files as $file) {
                $json = $this->readJson($file);
                $combined[] = $this->processFile($json, $file);
            }

            if ($dryRun) {
                DB::rollBack();

                return $this->buildFinal($combined);
            }

            DB::commit();

            return $this->buildFinal($combined);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process one snapshot document against the current (assumed active)
     * transaction and return that file's report payload.
     */
    private function processFile(array $json, string $filename): array
    {
        $this->existingRows = [];

        $this->loadExistingRows($filename);
        $this->importRecords($json, $filename);

        // The DB-backed imported total is computed while the transaction (and
        // any newly created rows) is visible, so re-runs that skip everything
        // still reconcile against what is already stored.
        $this->computeImportedTotal($json['jenis']);

        return $this->buildReport($json, $filename);
    }

    private function buildFinal(array $reports): array
    {
        return count($reports) === 1 ? $reports[0] : $this->mergeReports($reports);
    }

    /**
     * @return list<string> relative filenames of all .json snapshot files
     */
    private function jsonFiles(): array
    {
        $disk = Storage::disk(self::DISK);

        $paths = collect($disk->files())->filter(fn ($path) => str_ends_with($path, '.json'));

        if ($paths->isEmpty()) {
            throw new RuntimeException('No laporan JSON files found on the laporan disk.');
        }

        return $paths->values()->all();
    }

    private function readJson(string $filename): array
    {
        $disk = Storage::disk(self::DISK);
        if (! $disk->exists($filename)) {
            throw new RuntimeException("Laporan JSON not found: {$filename}");
        }

        $raw = $disk->get($filename);
        if (! is_string($raw)) {
            throw new RuntimeException("Unable to read laporan file: {$filename}");
        }

        $json = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        $this->validateDocument($json);

        return $json;
    }

    private function validateDocument(array $json): void
    {
        $required = ['jenis', 'periode', 'tanggal_laporan', 'records'];
        foreach ($required as $key) {
            if (! array_key_exists($key, $json)) {
                throw new RuntimeException("Laporan document is missing required key: {$key}");
            }
        }

        if (! is_array($json['records'])) {
            throw new RuntimeException('Laporan document "records" must be an array.');
        }
    }

    private function loadExistingRows(string $sourceFile): void
    {
        foreach (LaporanSnapshot::where('source_file', $sourceFile)->get() as $row) {
            if ($row->source_row !== null) {
                $this->existingRows[(int) $row->source_row] = true;
            }
        }
    }

    private function importRecords(array $json, string $sourceFile): void
    {
        $jenis = $json['jenis'];
        $this->report = $this->initReport($json);

        foreach ($json['records'] as $i => $record) {
            $rowNumber = $i + 1;

            if (isset($this->existingRows[$rowNumber])) {
                $this->report[$jenis]['skipped']++;

                continue;
            }

            $values = array_merge(
                $this->headerValues($json),
                $this->recordValues($record),
                [
                    'source_file' => $sourceFile,
                    'source_row' => $rowNumber,
                    'source_identifier' => $this->identifierFor($record, $rowNumber),
                ]
            );

            try {
                LaporanSnapshot::create($values);
                $this->existingRows[$rowNumber] = true;
                $this->report[$jenis]['created']++;
            } catch (\Throwable $e) {
                $this->failures[] = [
                    'file' => $sourceFile,
                    'index' => $rowNumber,
                    'reason' => $e->getMessage(),
                ];
            }
        }
    }

    private function initReport(array $json): array
    {
        return [
            $json['jenis'] => [
                'processed' => count($json['records']),
                'created' => 0,
                'skipped' => 0,
                'expected' => $this->expectedTotal($json),
                'imported' => 0.0,
                'reconciled' => false,
            ],
        ];
    }

    private function expectedTotal(array $json): float
    {
        if ($json['jenis'] === 'realisasi_pendapatan') {
            return (float) ($json['total_pendapatan']['realisasi'] ?? 0.0);
        }

        if ($json['jenis'] === 'posisi_kas') {
            return (float) ($json['jumlah_saldo_buku'] ?? 0.0);
        }

        return 0.0;
    }

    private function headerValues(array $json): array
    {
        return [
            'jenis' => $json['jenis'],
            'periode' => $json['periode'] ?? null,
            'tahun_anggaran' => $json['tahun_anggaran'] ?? null,
            'tanggal_laporan' => $json['tanggal_laporan'] ?? now()->toDateString(),
            'signed_by' => $json['signed_by'] ?? null,
        ];
    }

    private function recordValues(array $record): array
    {
        $pick = ['section', 'sub', 'tipe_baris', 'kode', 'level', 'uraian', 'keterangan',
            'target', 'realisasi_bulan_ini', 'realisasi_sd_bulan_lalu',
            'realisasi_sd_bulan_ini', 'persentase', 'lebih_kurang',
            'nilai', 'penerimaan', 'pengeluaran', 'sisa'];

        $values = [];
        foreach ($pick as $field) {
            if (array_key_exists($field, $record) && $record[$field] !== null) {
                $values[$field] = $record[$field];
            }
        }

        if (empty($values['tipe_baris'])) {
            $values['tipe_baris'] = 'rincian';
        }

        return $values;
    }

    private function identifierFor(array $record, int $index): string
    {
        if (! empty($record['kode'])) {
            return (string) $record['kode'];
        }

        if (! empty($record['section']) && ! empty($record['uraian'])) {
            return "{$record['section']}::{$record['uraian']}";
        }

        return (string) ($index + 1);
    }

    private function buildReport(array $json, string $sourceFile): array
    {
        $jenis = $json['jenis'];

        $report = $this->report[$jenis];
        $report['reconciled'] = abs($report['imported'] - $report['expected']) < 0.01;
        $this->report[$jenis] = $report;

        return [
            'file' => $sourceFile,
            'jenis' => $jenis,
            'periode' => $json['periode'] ?? null,
            'records' => [$jenis => $this->report[$jenis]],
            'failureCount' => count($this->failures),
            'failures' => $this->failures,
        ];
    }

    private function computeImportedTotal(string $jenis): void
    {
        $this->report[$jenis]['imported'] = (float) $this->importedTotalQuery($jenis);
    }

    private function importedTotalQuery(string $jenis): float
    {
        $query = static fn () => LaporanSnapshot::query()
            ->where('jenis', $jenis);

        if ($jenis === 'realisasi_pendapatan') {
            return $query()->where('level', 2)->sum('realisasi_sd_bulan_ini');
        }

        if ($jenis === 'posisi_kas') {
            return $query()
                ->whereIn('section', ['posisi_silpa_2025', 'posisi_realisasi_2026'])
                ->where('tipe_baris', 'total')
                ->where('uraian', 'like', 'Jumlah%')
                ->sum('sisa');
        }

        return 0.0;
    }

    private function mergeReports(array $reports): array
    {
        $merged = [
            'files' => array_column($reports, 'file'),
            'failureCount' => array_sum(array_column($reports, 'failureCount')),
            'failures' => array_merge(...array_map(fn ($r) => $r['failures'], $reports)),
            'records' => [],
        ];

        foreach ($reports as $r) {
            foreach ($r['records'] as $jenis => $stats) {
                $merged['records'][$jenis] = array_key_exists($jenis, $merged['records'])
                    ? $this->mergeStats($merged['records'][$jenis], $stats)
                    : $stats;
            }
        }

        return $merged;
    }

    private function mergeStats(array $a, array $b): array
    {
        $merged = $a;
        foreach (['processed', 'created', 'skipped'] as $k) {
            $merged[$k] += $b[$k];
        }
        $merged['expected'] += $b['expected'];
        $merged['imported'] += $b['imported'];
        $merged['reconciled'] = $merged['reconciled'] && $b['reconciled'];

        return $merged;
    }
}
