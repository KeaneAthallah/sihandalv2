<?php

namespace App\Imports\Definitions;

use App\Imports\MasterImportDefinition;
use App\Models\Rekening;

class RekeningImportDefinition extends MasterImportDefinition
{
    private const TIPE = ['kas', 'non-kas', 'pendapatan', 'belanja'];

    public function label(): string
    {
        return 'Rekening Kas';
    }

    public function filename(): string
    {
        return 'template-rekening-kas.xlsx';
    }

    public function headers(): array
    {
        return ['Kode', 'Nama Rekening', 'Tipe', 'Kode Induk (opsional)'];
    }

    public function exampleRow(): array
    {
        return [
            'Kode' => '1.1.1.02',
            'Nama Rekening' => 'Kas Contoh',
            'Tipe' => 'kas',
            'Kode Induk (opsional)' => '1.1.1',
        ];
    }

    public function referenceData(): array
    {
        return [
            'Tipe' => collect(self::TIPE)
                ->map(fn ($tipe) => ['Tipe' => $tipe])
                ->all(),
            'Rekening' => Rekening::orderBy('kode')->get()->map(fn ($rekening) => [
                'Kode' => $rekening->kode,
                'Nama' => $rekening->nama,
            ])->all(),
        ];
    }

    public function dropdowns(): array
    {
        return [
            'Tipe' => ['type' => 'inline', 'values' => self::TIPE],
            'Kode Induk (opsional)' => ['type' => 'reference', 'reference' => 'Rekening'],
        ];
    }

    public function parseRow(array $row): array
    {
        $kode = $this->requiredString($row, 'Kode', 'Kode');
        $tipe = $this->requiredString($row, 'Tipe', 'Tipe');

        if (! in_array($tipe, self::TIPE, true)) {
            $this->fail("Tipe '{$tipe}' tidak valid. Gunakan: ".implode(', ', self::TIPE).'.');
        }

        $kodeInduk = $this->optionalString($row, 'Kode Induk (opsional)');

        if ($kodeInduk !== null && strtolower($kodeInduk) === strtolower($kode)) {
            $this->fail('Rekening tidak dapat menjadi detail dari dirinya sendiri.');
        }

        $parentId = $this->resolveRekeningByKode($kodeInduk);

        if ($parentId !== null) {
            $parent = Rekening::find($parentId);

            if ($parent !== null && $parent->tipe !== $tipe) {
                $this->fail('Rekening detail harus bertipe sama dengan rekening induknya.');
            }
        }

        return [
            'kode' => $kode,
            'nama' => $this->requiredString($row, 'Nama Rekening', 'Nama Rekening'),
            'tipe' => $tipe,
            'parent_id' => $parentId,
        ];
    }

    public function importRow(array $row): string
    {
        $rekening = Rekening::updateOrCreate(
            ['kode' => $row['kode']],
            [
                'nama' => $row['nama'],
                'tipe' => $row['tipe'],
                'parent_id' => $row['parent_id'],
            ]
        );

        return $rekening->wasRecentlyCreated ? 'created' : 'updated';
    }
}
