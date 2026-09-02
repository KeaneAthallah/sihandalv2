<?php

namespace App\Support;

use RuntimeException;

/**
 * Minimal CSV reader that maps raw records to associative arrays using a
 * header row, stripping the UTF-8 BOM, trimming cell values, and collapsing
 * internal whitespace runs. RFC/Excel-style quoted fields are supported via
 * the underlying fgetcsv().
 */
class CsvReader
{
    /** @var resource */
    private $handle;

    /** @var list<string> */
    private array $header = [];

    public function __construct(private string $path)
    {
        if (! file_exists($path)) {
            throw new RuntimeException("CSV file not found: {$path}");
        }
    }

    /**
     * Open the file and return the normalized header names.
     *
     * @return list<string>
     */
    public function open(): array
    {
        $handle = fopen($this->path, 'r');
        if ($handle === false) {
            throw new RuntimeException("Unable to open CSV file: {$this->path}");
        }
        $this->handle = $handle;

        $header = fgetcsv($handle, 0, ',', '"', '\\');
        if ($header === false) {
            throw new RuntimeException("CSV file has no header row: {$this->path}");
        }

        $this->header = array_map($this->normalize(...), $header);

        return $this->header;
    }

    /**
     * Read the next record as an associative array, or null at end of file.
     *
     * @return array<string, string|null>|null
     */
    public function read(): ?array
    {
        if ($this->handle === null) {
            $this->open();
        }

        $row = fgetcsv($this->handle, 0, ',', '"', '\\');
        if ($row === false) {
            $this->close();

            return null;
        }

        $record = [];
        foreach ($this->header as $i => $name) {
            $record[$name] = isset($row[$i]) ? $this->normalize($row[$i]) : null;
        }

        return $record;
    }

    public function close(): void
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    private function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}
