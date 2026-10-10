<?php

namespace App\Services;

use App\Imports\ImportDefinition;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Builds the per-module template workbook: a "Data" sheet with a
 * bold styled header row, an italic example row, frozen top row,
 * autofilter, auto-sized columns and dropdown validations, plus a
 * "Referensi" sheet listing every lookup block side by side.
 */
class ExcelTemplateService
{
    private const DATA_ROWS = 1000;

    public function build(ImportDefinition $definition): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');

        $headers = $definition->headers();
        $this->renderHeaderRow($sheet, $headers);
        $this->renderExampleRow($sheet, $headers, $definition->exampleRow());

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}1");

        foreach ($headers as $index => $_) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index + 1))->setAutoSize(true);
        }

        $referenceSheet = $spreadsheet->createSheet();
        $referenceSheet->setTitle('Referensi');
        $blockPositions = $this->renderReferenceBlocks($referenceSheet, $definition->referenceData());

        $this->applyDropdowns($sheet, $headers, $definition->dropdowns(), $blockPositions);

        return $spreadsheet;
    }

    public function stream(ImportDefinition $definition): StreamedResponse
    {
        $spreadsheet = $this->build($definition);
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = $definition->filename();

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  list<string>  $headers
     */
    private function renderHeaderRow(Worksheet $sheet, array $headers): void
    {
        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue("{$column}1", $header);

            $style = $sheet->getStyle("{$column}1");
            $style->getFont()->setBold(true);
            $style->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2E8F0');
        }
    }

    /**
     * @param  list<string>  $headers
     * @param  array<string, scalar|null>  $example
     */
    private function renderExampleRow(Worksheet $sheet, array $headers, array $example): void
    {
        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue("{$column}2", $example[$header] ?? null);

            $sheet->getStyle("{$column}2")->getFont()
                ->setItalic(true)
                ->getColor()->setARGB('FF94A3B8');
        }
    }

    /**
     * @param  array<string, list<array<string, scalar>>>  $blocks
     * @return array<string, array{column: string, firstRow: int, count: int}>
     */
    private function renderReferenceBlocks(Worksheet $sheet, array $blocks): array
    {
        $positions = [];
        $column = 1;

        foreach ($blocks as $title => $rows) {
            $letter = Coordinate::stringFromColumnIndex($column);
            $sheet->setCellValue("{$letter}1", $title);
            $sheet->getStyle("{$letter}1")->getFont()->setBold(true)->setSize(12);

            $keys = $rows === [] ? [] : array_keys($rows[0]);
            foreach ($keys as $index => $key) {
                $keyColumn = Coordinate::stringFromColumnIndex($column + $index);
                $sheet->setCellValue("{$keyColumn}2", $key);
                $sheet->getStyle("{$keyColumn}2")->getFont()->setBold(true);
            }

            foreach ($rows as $offset => $row) {
                foreach ($keys as $index => $key) {
                    $keyColumn = Coordinate::stringFromColumnIndex($column + $index);
                    $sheet->setCellValue("{$keyColumn}".(3 + $offset), $row[$key] ?? null);
                }
            }

            $positions[$title] = [
                'column' => Coordinate::stringFromColumnIndex($column),
                'firstRow' => 3,
                'count' => count($rows),
            ];

            $column += max(count($keys), 1) + 1;
        }

        return $positions;
    }

    /**
     * @param  list<string>  $headers
     * @param  array<string, array{type: string, values?: list<string>, reference?: string}>  $dropdowns
     * @param  array<string, array{column: string, firstRow: int, count: int}>  $blockPositions
     */
    private function applyDropdowns(Worksheet $sheet, array $headers, array $dropdowns, array $blockPositions): void
    {
        foreach ($dropdowns as $header => $dropdown) {
            $index = array_search($header, $headers, true);

            if ($index === false) {
                continue;
            }

            $column = Coordinate::stringFromColumnIndex($index + 1);
            $validation = new DataValidation;
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowErrorMessage(true);

            if (($dropdown['type'] ?? null) === 'reference') {
                $position = $blockPositions[$dropdown['reference'] ?? ''] ?? null;

                if ($position === null || $position['count'] === 0) {
                    continue;
                }

                $lastRow = $position['firstRow'] + $position['count'] - 1;
                $validation->setFormula1(
                    "=Referensi!{$position['column']}{$position['firstRow']}:{$position['column']}{$lastRow}"
                );
            } else {
                $validation->setFormula1('"'.implode(',', $dropdown['values'] ?? []).'"');
            }

            $sheet->setDataValidation("{$column}2:{$column}".self::DATA_ROWS, $validation);
        }
    }
}
