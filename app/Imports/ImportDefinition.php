<?php

namespace App\Imports;

/**
 * Contract for a single master-data module that can be imported
 * from — and templated to — an Excel/CSV workbook.
 */
interface ImportDefinition
{
    /**
     * Human-readable module label, used in flash messages.
     */
    public function label(): string;

    /**
     * Suggested filename for the downloadable template.
     */
    public function filename(): string;

    /**
     * Canonical column headers, in order.
     *
     * @return list<string>
     */
    public function headers(): array;

    /**
     * Example row rendered greyed-out under the header.
     *
     * @return array<string, scalar|null>
     */
    public function exampleRow(): array;

    /**
     * Lookup blocks rendered side by side on the "Referensi" sheet.
     *
     * @return array<string, list<array<string, scalar>>>
     */
    public function referenceData(): array;

    /**
     * Dropdown validations for the Data sheet, keyed by header.
     * Either ['type' => 'inline', 'values' => list<string>] or
     * ['type' => 'reference', 'reference' => string] pointing at a
     * referenceData() block title.
     *
     * @return array<string, array{type: string, values?: list<string>, reference?: string}>
     */
    public function dropdowns(): array;

    /**
     * Map a workbook header row onto the canonical headers.
     *
     * @param  list<string|null>  $sheetHeader
     * @return array<string, int> canonical header => column index
     */
    public function headerMap(array $sheetHeader): array;

    /**
     * Validate and normalize one sheet row. Throws ImportRowException
     * with a friendly message on failure.
     *
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    public function parseRow(array $row): array;

    /**
     * Apply one parsed row inside the import transaction.
     *
     * @param  array<string, mixed>  $row
     */
    public function importRow(array $row): string;
}
