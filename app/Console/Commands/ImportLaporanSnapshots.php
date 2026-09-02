<?php

namespace App\Console\Commands;

use App\Services\LaporanSnapshotImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportLaporanSnapshots extends Command
{
    protected $signature = 'app:import-laporan-snapshots
        {file? : JSON filename relative to the laporan disk (defaults to all .json files)}
        {--dry-run : Validate the import inside a rolled-back transaction without persisting anything}
        {--force : Import without prompting}';

    protected $description = 'Import BPKAD PDF report snapshots (from parsed JSON on the laporan disk) into laporan_snapshots, with reconciliation and traceability.';

    public function handle(): int
    {
        $file = $this->argument('file');

        if ($file !== null && ! Storage::disk('laporan')->exists($file)) {
            $this->error("File not found on laporan disk: {$file}");

            return 1;
        }

        if (! $this->option('force') && $file === null && ! $this->confirm('Import all laporan JSON snapshots on the laporan disk? Continue?')) {
            $this->warn('Aborted.');

            return 1;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run: validating inside a rolled-back transaction. Nothing will be written.');
        }

        $service = app(LaporanSnapshotImportService::class);

        try {
            $report = $file !== null
                ? $service->import($file, (bool) $this->option('dry-run'))
                : $service->importDir((bool) $this->option('dry-run'));
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
            $this->error('Import reported failed row(s). Check the listing below.');

            return 1;
        }

        return $this->reconciled($report) ? 0 : 1;
    }

    private function renderReport(array $report): void
    {
        $this->newLine();
        $this->info('=== Laporan snapshots import report ===');

        foreach ($report['records'] as $jenis => $stats) {
            $this->info("--- {$jenis} ---");
            $this->table(
                ['Item', 'Value'],
                [
                    ['Records processed', number_format($stats['processed'])],
                    ['Created', number_format($stats['created'])],
                    ['Already present (skipped)', number_format($stats['skipped'])],
                    ['Expected total', 'Rp '.number_format($stats['expected'], 2, ',', '.')],
                    ['Imported total', 'Rp '.number_format($stats['imported'], 2, ',', '.')],
                    ['Reconciled', $stats['reconciled'] ? 'YES' : 'NO'],
                ]
            );
        }

        if ($report['failureCount'] > 0) {
            $this->newLine();
            $this->error('--- Failed rows ---');
            foreach ($report['failures'] as $failure) {
                $this->error("  {$failure['file']} row {$failure['index']}: {$failure['reason']}");
            }
        }
    }

    private function reconciled(array $report): bool
    {
        foreach ($report['records'] as $stats) {
            if (! $stats['reconciled']) {
                return false;
            }
        }

        return true;
    }
}
