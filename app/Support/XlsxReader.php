<?php

namespace App\Support;

use Carbon\Carbon;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * Minimal, dependency-free reader for the Sihandal import workbooks.
 *
 * The PENERIMAAN workbook stores headers as worksheet rows, records across a
 * single "Data Input" sheet, and encodes dates as Excel serial numbers with a
 * date number format. This reader resolves shared strings, inline strings,
 * numeric values, and (via number-format detection) converts date serials into
 * 'Y-m-d' strings — without requiring a third-party spreadsheet package.
 *
 * It intentionally returns raw values keyed by zero-based column index.
 */
class XlsxReader
{
    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    private const NS_PKG = 'http://schemas.openxmlformats.org/package/2006/relationships';

    private string $sharedStringsFile = 'xl/sharedStrings.xml';

    private string $workbookFile = 'xl/workbook.xml';

    private string $relsFile = 'xl/_rels/workbook.xml.rels';

    private string $stylesFile = 'xl/styles.xml';

    /** @var array<int,string> */
    private array $sharedStrings = [];

    /** @var array<int,true> style indexes whose format is a date */
    private array $dateStyleIndexes = [];

    /** @var array<string,string> sheet name => sheet xml path */
    private array $sheetPaths = [];

    public function __construct(private string $path)
    {
        if (! file_exists($path)) {
            throw new RuntimeException("XLSX file not found: {$path}");
        }
    }

    /**
     * Read a worksheet by name and return rows keyed by row number (1-based),
     * each value being a scalar resolved from the workbook.
     *
     * @return array<int, array<int, int|float|string|null>>
     */
    public function readSheet(string $sheetName): array
    {
        $zip = $this->openZip();
        $this->loadSharedStrings($zip);
        $this->loadStyles($zip);
        $this->loadWorkbook($zip);

        if (! isset($this->sheetPaths[$sheetName])) {
            $zip->close();
            throw new RuntimeException("Worksheet '{$sheetName}' not found in workbook.");
        }

        $xml = $zip->getFromName($this->sheetPaths[$sheetName]);
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException("Could not read worksheet XML for '{$sheetName}'.");
        }

        return $this->parseSheet($xml);
    }

    /** @return list<string> sheet names in workbook order */
    public function sheetNames(): array
    {
        if ($this->sheetPaths === []) {
            $zip = $this->openZip();
            $this->loadWorkbook($zip);
            $zip->close();
        }

        return array_keys($this->sheetPaths);
    }

    private function openZip(): ZipArchive
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP zip extension is required to read XLSX files.');
        }

        $zip = new ZipArchive;
        if ($zip->open($this->path) !== true) {
            throw new RuntimeException("Unable to open XLSX file: {$this->path}");
        }

        return $zip;
    }

    private function attr(SimpleXMLElement $element, string $name, ?string $namespace = null): ?string
    {
        $value = $element->attributes($namespace)[$name] ?? null;

        return $value === null ? null : (string) $value;
    }

    private function loadSharedStrings(ZipArchive $zip): void
    {
        if ($zip->locateName($this->sharedStringsFile) === false) {
            return;
        }

        $xml = simplexml_load_string($zip->getFromName($this->sharedStringsFile));
        if ($xml === false) {
            return;
        }

        foreach ($xml->children(self::NS_MAIN)->si as $si) {
            $text = '';
            foreach ($si->children(self::NS_MAIN)->t as $t) {
                $text .= (string) $t;
            }
            foreach ($si->children(self::NS_MAIN)->r as $r) {
                $text .= (string) $r->children(self::NS_MAIN)->t;
            }
            $this->sharedStrings[] = $text;
        }
    }

    private function loadStyles(ZipArchive $zip): void
    {
        if ($zip->locateName($this->stylesFile) === false) {
            return;
        }

        $xml = simplexml_load_string($zip->getFromName($this->stylesFile));
        if ($xml === false) {
            return;
        }

        $root = $xml->children(self::NS_MAIN);

        $customFormats = [];
        if (isset($root->numFmts->numFmt)) {
            foreach ($root->numFmts->numFmt as $numFmt) {
                $customFormats[(int) $this->attr($numFmt, 'numFmtId')] = (string) $this->attr($numFmt, 'formatCode');
            }
        }

        $isDateFormat = function (int $numFmtId, ?string $formatCode): bool {
            if ($formatCode !== null && $formatCode !== 'General') {
                return (bool) preg_match('/(^|[;"_\\s])[dmyhs]/i', $formatCode);
            }

            // Built-in Excel date number format IDs.
            return in_array($numFmtId, [14, 15, 16, 17, 18, 19, 20, 21, 22, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 45, 46, 47, 50, 51, 52, 53, 54, 55, 56, 57, 58], true);
        };

        if (isset($root->cellXfs)) {
            $index = 0;

            foreach ($root->cellXfs->xf as $xf) {
                $numFmtId = (int) ($this->attr($xf, 'numFmtId') ?? 0);
                $code = $customFormats[$numFmtId] ?? null;
                if ($isDateFormat($numFmtId, $code)) {
                    $this->dateStyleIndexes[$index] = true;
                }

                $index++;
            }
        }
    }

    private function loadWorkbook(ZipArchive $zip): void
    {
        $ridToName = [];
        $orders = [];

        $wbXml = $zip->getFromName($this->workbookFile);
        if ($wbXml !== false) {
            $xml = simplexml_load_string($wbXml);
            if ($xml !== false) {
                foreach ($xml->children(self::NS_MAIN)->sheets->sheet as $i => $sheet) {
                    $name = $this->attr($sheet, 'name');
                    $rid = $this->attr($sheet, 'id', self::NS_REL);
                    $ridToName[$rid] = $name;
                    $orders[$name] = $i;
                }
            }
        }

        $ridToTarget = [];
        $relsXml = $zip->getFromName($this->relsFile);
        if ($relsXml !== false) {
            $rels = simplexml_load_string($relsXml);
            if ($rels !== false) {
                foreach ($rels->children(self::NS_PKG)->Relationship as $rel) {
                    $ridToTarget[$this->attr($rel, 'Id')] = $this->attr($rel, 'Target');
                }
            }
        }

        $paths = [];
        foreach ($ridToName as $rid => $name) {
            $target = $ridToTarget[$rid] ?? null;
            if ($target === null) {
                continue;
            }
            $paths[$name] = str_starts_with($target, '/')
                ? ltrim($target, '/')
                : 'xl/'.ltrim($target, '/');
        }

        // Order sheets by workbook order.
        $ordered = [];
        asort($orders);
        foreach ($orders as $name => $_) {
            if (isset($paths[$name])) {
                $ordered[$name] = $paths[$name];
            }
        }

        $this->sheetPaths = $ordered !== [] ? $ordered : $paths;
    }

    /**
     * @param  string  $xml  raw worksheet XML
     * @return array<int, array<int, int|float|string|null>>
     */
    private function parseSheet(string $xml): array
    {
        $sheet = simplexml_load_string($xml);
        if ($sheet === false) {
            throw new RuntimeException('Unable to parse worksheet XML.');
        }

        $rows = [];
        $columns = $sheet->children(self::NS_MAIN)->sheetData->row;

        foreach ($columns as $rowXml) {
            $rowNum = (int) $this->attr($rowXml, 'r');
            $row = [];

            foreach ($rowXml->children(self::NS_MAIN)->c as $cell) {
                $ref = $this->attr($cell, 'r');
                $colIndex = $this->columnIndex($ref ?? '');
                if ($colIndex < 0) {
                    continue;
                }

                $type = $this->attr($cell, 't') ?? '';
                $style = $this->attr($cell, 's') !== null ? (int) $this->attr($cell, 's') : null;
                $raw = isset($cell->children(self::NS_MAIN)->v)
                    ? (string) $cell->children(self::NS_MAIN)->v
                    : null;

                $value = match ($type) {
                    's' => $raw !== null ? ($this->sharedStrings[(int) $raw] ?? null) : null,
                    'inlineStr' => trim((string) $cell->children(self::NS_MAIN)->is->children(self::NS_MAIN)->t),
                    'str' => $raw,
                    'b' => $raw === '1' ? true : false,
                    default => $this->numericValue($raw, $style),
                };

                $row[$colIndex] = $value;
            }

            $rows[$rowNum] = $row;
        }

        return $rows;
    }

    /**
     * Resolve a numeric cell; convert to a date string when the style is a
     * date format.
     */
    private function numericValue(?string $raw, ?int $style): int|float|string|null
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if ($style !== null && isset($this->dateStyleIndexes[$style])) {
            $serial = (float) $raw;

            return $this->excelDate($serial);
        }

        if (ctype_digit(ltrim($raw, '-'))) {
            return (int) $raw;
        }

        return (float) $raw;
    }

    private function excelDate(float $serial): string
    {
        $epoch = Carbon::parse('1899-12-30');

        return $epoch->copy()->addDays((int) floor($serial))->format('Y-m-d');
    }

    /**
     * Convert an Excel cell reference ("B9") into a zero-based column index.
     */
    private function columnIndex(string $ref): int
    {
        $letters = '';
        foreach (str_split($ref) as $char) {
            if (ctype_alpha($char)) {
                $letters .= $char;
            } else {
                break;
            }
        }

        if ($letters === '') {
            return -1;
        }

        $index = 0;
        foreach (str_split($letters) as $char) {
            $index = $index * 26 + (ord(strtoupper($char)) - 64);
        }

        return $index - 1;
    }
}
