<?php

namespace App\Imports\Definitions;

use App\Imports\MasterImportDefinition;
use App\Models\Opd;
use App\Models\Upt;

class UptImportDefinition extends MasterImportDefinition
{
    public function label(): string
    {
        return 'UPT';
    }

    public function filename(): string
    {
        return 'template-upt.xlsx';
    }

    public function headers(): array
    {
        return ['Kode', 'Nama UPT', 'OPD'];
    }

    public function exampleRow(): array
    {
        return [
            'Kode' => 'UPT-01',
            'Nama UPT' => 'UPT Contoh',
            'OPD' => 'Dinas Pendidikan',
        ];
    }

    public function referenceData(): array
    {
        return [
            'OPD' => Opd::orderBy('nama')->get()->map(fn ($opd) => [
                'Kode' => $opd->kode,
                'Nama' => $opd->nama,
            ])->all(),
        ];
    }

    public function dropdowns(): array
    {
        return [
            'OPD' => ['type' => 'reference', 'reference' => 'OPD'],
        ];
    }

    public function parseRow(array $row): array
    {
        return [
            'kode' => $this->requiredString($row, 'Kode', 'Kode'),
            'nama' => $this->requiredString($row, 'Nama UPT', 'Nama UPT'),
            'opd_id' => $this->resolveOpd($this->requiredString($row, 'OPD', 'OPD')),
        ];
    }

    public function importRow(array $row): string
    {
        $upt = Upt::updateOrCreate(
            ['kode' => $row['kode']],
            ['nama' => $row['nama'], 'opd_id' => $row['opd_id']]
        );

        return $upt->wasRecentlyCreated ? 'created' : 'updated';
    }
}
