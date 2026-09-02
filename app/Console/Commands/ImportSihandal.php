<?php

namespace App\Console\Commands;

use App\Services\SihandalImportService;
use Illuminate\Console\Command;

class ImportSihandal extends Command
{
    protected $signature = 'app:import-sihandal
        {csv? : Path to the budget CSV (defaults to database/seeders/data/sumberdana26.csv)}
        {xlsx? : Path to the revenue XLSX (defaults to database/seeders/data/penerimaan-2026.xlsx)}
        {--fresh : Wipe all hierarchy/budget/revenue tables before importing}
        {--dry-run : Analyze and validate the full import inside a rolled-back transaction without persisting anything}
        {--force : Confirm destructive/large import without prompting}';

    protected $description = 'Import the full 2026 Sihandal dataset (budget CSV + revenue XLSX) into the normalized hierarchy, with reconciliation and per-row traceability.';

    private const DEFAULT_CSV = 'database/seeders/data/sumberdana26.csv';

    private const DEFAULT_XLSX = 'database/seeders/data/penerimaan-2026.xlsx';

    public function handle(): int
    {
        $csv = $this->argument('csv') ? base_path($this->argument('csv')) : base_path(self::DEFAULT_CSV);
        $xlsx = $this->argument('xlsx') ? base_path($this->argument('xlsx')) : base_path(self::DEFAULT_XLSX);

        $missing = [];
        if (! file_exists($csv)) {
            $missing[] = $csv;
        }
        if (! file_exists($xlsx)) {
            $missing[] = $xlsx;
        }

        if ($missing !== []) {
            foreach ($missing as $path) {
                $this->error("File not found: {$path}");
            }

            return 1;
        }

        if ($this->option('fresh') && ! $this->option('force') && ! $this->confirm('Fresh import will wipe the hierarchy/budget/revenue tables. Continue?')) {
            $this->warn('Aborted.');

            return 1;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run: validating the import inside a rolled-back transaction. Nothing will be written.');
        }

        $this->info('Starting Sihandal import…');

        try {
            $report = app(SihandalImportService::class)->import(
                $csv,
                $xlsx,
                (bool) $this->option('fresh'),
                (bool) $this->option('dry-run')
            );
        } catch (\Throwable $e) {
            $this->error('Import failed and was rolled back. Nothing was written.');
            $this->error($e->getMessage());

            return 1;
        }

        $this->renderReport($report);

        if ($this->option('dry-run')) {
            $this->info('Dry run completed. No records were persisted.');

            return $report['failureCount'] > 0 ? 1 : 0;
        }

        if ($report['failureCount'] > 0) {
            $this->error("Import reported {$report['failureCount']} failed row(s). Check the actionable listing below.");

            return 1;
        }

        return $this->reconciled($report) ? 0 : 1;
    }

    private function renderReport(array $report): void
    {
        $this->newLine();
        $this->info('=== Sihandal import report (STEP 29) ===');

        $this->table(
            ['Record type', 'Total in DB'],
            [
                ['OPD', number_format($report['created']['opd'])],
                ['Program', number_format($report['created']['program'])],
                ['Rekening', number_format($report['created']['rekening'])],
                ['Sumber dana', number_format($report['created']['sumberDana'])],
                ['Tahun anggaran', number_format($report['created']['tahunAnggaran'])],
                ['Kegiatan', number_format($report['created']['kegiatan'])],
                ['Sub kegiatan', number_format($report['created']['subKegiatan'])],
                ['Belanja (budget lines)', number_format($report['created']['belanja'])],
                ['Penerimaan (revenue rows)', number_format($report['created']['penerimaan'])],
            ]
        );

        $b = $report['budget'];
        $this->info('--- Budget reconciliation (CSV) ---');
        $this->table(
            ['Item', 'Value'],
            [
                ['CSV rows processed', number_format($b['processed'])],
                ['Budget lines created', number_format($b['created'])],
                ['Already present (skipped)', number_format($b['skipped'])],
                ['Source SUM(PAGU) raw', 'Rp '.number_format($b['expectedSumRaw'], 2, ',', '.')],
                ['Source SUM(ROUND(PAGU,2))', 'Rp '.number_format($b['expectedSumRounded'], 2, ',', '.')],
                ['Rounding diff (raw - rounded)', 'Rp '.number_format($b['roundingDiff'], 2, ',', '.')],
                ['Imported Belanja SUM(pagu)', 'Rp '.number_format($b['actualSum'], 2, ',', '.')],
                ['Reconciled', $b['reconciled'] ? 'YES' : 'NO'],
            ]
        );

        $r = $report['revenue'];
        $this->info('--- Revenue reconciliation (XLSX) ---');
        $this->table(
            ['Item', 'Value'],
            [
                ['Source data rows', number_format($r['sourceRows'])],
                ['Revenue rows created', number_format($r['created'])],
                ['Already present (skipped)', number_format($r['skipped'])],
                ['Source SUM(H)', 'Rp '.number_format($r['expectedSum'], 2, ',', '.')],
                ['Imported Penerimaan SUM(realisasi)', 'Rp '.number_format($r['actualSum'], 2, ',', '.')],
                ['Reconciled', $r['reconciled'] ? 'YES' : 'NO'],
            ]
        );

        if ($report['failureCount'] > 0) {
            $this->newLine();
            $this->error('--- Failed rows ---');
            foreach ($report['failures'] as $failure) {
                $this->error("  {$failure['file']} row {$failure['row']}: {$failure['reason']}");
            }
        }
    }

    private function reconciled(array $report): bool
    {
        return $report['budget']['reconciled'] && $report['revenue']['reconciled'];
    }
}
