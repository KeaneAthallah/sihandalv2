<?php

namespace Tests\Fixtures;

use ZipArchive;

/**
 * Builds a minimal, valid OOXML workbook for testing XlsxReader /
 * SihandalImportService without a real spreadsheet tool.
 *
 * Produces a single "Data Input" sheet with a SALDO AWAL row (row 8) followed
 * by revenue data rows, D/E cells styled as dates (built-in numFmtId 14) and
 * amounts in column H. It deliberately mirrors the shape of the production
 * PENERIMAAN workbook.
 */
class XlsxFixture
{
    public static function build(string $path): void
    {
        if (file_exists($path)) {
            @unlink($path);
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Unable to create xlsx fixture.');
        }

        $strings = [
            0 => 'SALDO AWAL 2026',
            1 => 'SILPA',
            2 => 'PENDAPATAN ASLI DAERAH (PAD)',
            3 => 'Desc A',
            4 => 'Desc B',
            5 => 'Pendapatan dari BLUD',
            6 => 'Desc C',
        ];

        $sharedXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($strings).'" uniqueCount="'.count($strings).'">';
        foreach ($strings as $text) {
            $sharedXml .= '<si><t><![CDATA['.$text.']]></t></si>';
        }
        $sharedXml .= '</sst>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<cellXfs count="2">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="14" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            .'</cellXfs></styleSheet>';

        $rows = [
            8 => [
                'D' => null, 'E' => null, 'G' => 'str:0', 'H' => 1000.50, 'J' => 'str:1',
            ],
            9 => [
                'D' => ['date', 46024], 'E' => ['date', 46024], 'G' => 'str:3', 'H' => 250.00, 'J' => 'str:2',
            ],
            10 => [
                'D' => ['date', 46024], 'E' => ['date', 46024], 'G' => 'str:4', 'H' => 125.00, 'J' => 'str:2',
            ],
            11 => [
                'D' => ['date', 46024], 'E' => ['date', 46024], 'G' => 'str:6', 'H' => 74.50, 'J' => 'str:5',
            ],
            12 => [
                'D' => null, 'E' => null, 'G' => 'str:6', 'H' => 10.00, 'J' => 'str:5',
            ],
        ];

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

        foreach ($rows as $r => $cells) {
            $sheetXml .= '<row r="'.$r.'">';
            foreach ($cells as $col => $value) {
                if ($col === 'D' || $col === 'E') {
                    if ($value !== null) {
                        $sheetXml .= '<c r="'.$col.$r.'" s="1"><v>'.$value[1].'</v></c>';
                    }

                    continue;
                }

                if (is_string($value)) {
                    [$type, $index] = explode(':', $value);
                    $sheetXml .= '<c r="'.$col.$r.'" t="s"><v>'.$index.'</v></c>';
                } else {
                    $sheetXml .= '<c r="'.$col.$r.'"><v>'.$value.'</v></c>';
                }
            }
            $sheetXml .= '</row>';
        }

        $sheetXml .= '</sheetData></worksheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'
            .'<sheet name="Data Input" sheetId="1" r:id="rId1"/>'
            .'</sheets></workbook>';

        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';

        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $relsXml);
        $zip->addFromString('xl/sharedStrings.xml', $sharedXml);
        $zip->addFromString('xl/styles.xml', $stylesXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();
    }

    public static function rekeningsPaguReconciled(): float
    {
        return 1000.50 + 250.00 + 125.00 + 74.50 + 10.00;
    }
}
