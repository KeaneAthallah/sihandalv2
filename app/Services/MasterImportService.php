<?php

namespace App\Services;

use App\Imports\ImportDefinition;
use App\Imports\ImportRowException;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * All-or-nothing import engine. Every row is parsed and validated
 * first; any failure is collected and reported with its spreadsheet
 * row number and nothing is written. Only when all rows are valid
 * are they applied inside a single transaction.
 */
class MasterImportService
{
    /**
     * @return array{processed: int, created: int, updated: int, failed: int, errors: list<string>}
     */
    public function import(string $path, string $extension, ImportDefinition $definition): array
    {
        $reader = IOFactory::createReader(match (strtolower($extension)) {
            'csv' => 'Csv',
            'xls' => 'Xls',
            default => 'Xlsx',
        });

        $rows = $reader->load($path)->getActiveSheet()->toArray();

        if ($rows === []) {
            return $this->failed(0, ['File tidak memiliki baris data.']);
        }

        $headerMap = $definition->headerMap($this->stringRow($rows[0]));

        $missing = array_diff($definition->headers(), array_keys($headerMap));
        if ($missing !== []) {
            return $this->failed(0, [
                'Kolom berikut tidak ditemukan pada file: '.implode(', ', $missing).'.',
            ]);
        }

        $parsed = [];
        $errors = [];
        $processed = 0;

        foreach ($rows as $index => $cells) {
            if ($index === 0) {
                continue;
            }

            $row = $this->stringRow($cells);
            $mapped = [];
            foreach ($headerMap as $header => $column) {
                $mapped[$header] = $row[$column] ?? null;
            }

            if ($this->isEmpty($mapped)) {
                continue;
            }

            $processed++;
            $rowNumber = $index + 1;

            try {
                $parsed[$rowNumber] = $definition->parseRow($mapped);
            } catch (ImportRowException $e) {
                $errors[] = "Baris {$rowNumber}: ".$e->getMessage();
            }
        }

        if ($errors !== []) {
            return $this->failed($processed, $errors);
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($definition, $parsed, &$created, &$updated) {
            foreach ($parsed as $row) {
                if ($definition->importRow($row) === 'created') {
                    $created++;
                } else {
                    $updated++;
                }
            }
        });

        return [
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'failed' => 0,
            'errors' => [],
        ];
    }

    /**
     * @param  list<string>  $errors
     * @return array{processed: int, created: int, updated: int, failed: int, errors: list<string>}
     */
    private function failed(int $processed, array $errors): array
    {
        return [
            'processed' => $processed,
            'created' => 0,
            'updated' => 0,
            'failed' => count($errors),
            'errors' => $errors,
        ];
    }

    /**
     * @param  list<int|float|string|null>  $cells
     * @return list<string|null>
     */
    private function stringRow(array $cells): array
    {
        return array_map(
            fn ($cell) => $cell === null ? null : (string) $cell,
            $cells
        );
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function isEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }
}
