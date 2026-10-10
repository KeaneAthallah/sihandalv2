<?php

namespace App\Imports\Definitions;

use App\Imports\MasterImportDefinition;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\TahunAnggaran;

class PenerimaanImportDefinition extends MasterImportDefinition
{
    public function label(): string
    {
        return 'Penerimaan';
    }

    public function filename(): string
    {
        return 'template-penerimaan.xlsx';
    }

    public function headers(): array
    {
        return [
            'OPD',
            'Rekening Utama (opsional)',
            'Sub Rekening (opsional)',
            'Tahun Anggaran (opsional)',
            'Target',
        ];
    }

    public function exampleRow(): array
    {
        return [
            'OPD' => 'Dinas Pendidikan',
            'Rekening Utama (opsional)' => '4.1.1',
            'Sub Rekening (opsional)' => '4.1.1.01',
            'Tahun Anggaran (opsional)' => '2026',
            'Target' => '1000000000',
        ];
    }

    public function referenceData(): array
    {
        return [
            'OPD' => Opd::orderBy('nama')->get()->map(fn ($opd) => [
                'Kode' => $opd->kode,
                'Nama' => $opd->nama,
            ])->all(),
            'Rekening Utama' => Rekening::where('tipe', 'pendapatan')
                ->orderBy('kode')
                ->get()
                ->map(fn ($rekening) => [
                    'Kode' => $rekening->kode,
                    'Nama' => $rekening->nama,
                ])
                ->all(),
            'Sub Rekening' => Rekening::where('tipe', 'pendapatan')
                ->whereNotNull('parent_id')
                ->orderBy('kode')
                ->get()
                ->map(fn ($rekening) => [
                    'Kode' => $rekening->kode,
                    'Nama' => $rekening->nama,
                ])
                ->all(),
            'Tahun Anggaran' => TahunAnggaran::orderByDesc('tahun')->get()->map(fn ($tahunAnggaran) => [
                'Tahun' => $tahunAnggaran->tahun,
            ])->all(),
        ];
    }

    public function dropdowns(): array
    {
        return [
            'OPD' => ['type' => 'reference', 'reference' => 'OPD'],
            'Rekening Utama (opsional)' => ['type' => 'reference', 'reference' => 'Rekening Utama'],
            'Sub Rekening (opsional)' => ['type' => 'reference', 'reference' => 'Sub Rekening'],
            'Tahun Anggaran (opsional)' => ['type' => 'reference', 'reference' => 'Tahun Anggaran'],
        ];
    }

    public function parseRow(array $row): array
    {
        $target = $this->requiredNumeric($row, 'Target', 'Target');

        $rekeningId = $this->resolveRekening($this->optionalString($row, 'Rekening Utama (opsional)'));

        if ($rekeningId !== null) {
            $rekening = Rekening::find($rekeningId);

            if ($rekening !== null && $rekening->tipe !== 'pendapatan') {
                $this->fail('Rekening utama penerimaan harus bertipe pendapatan.');
            }
        }

        $subRekeningId = $this->resolveRekening($this->optionalString($row, 'Sub Rekening (opsional)'));

        if ($subRekeningId !== null) {
            $subRekening = Rekening::find($subRekeningId);

            if ($rekeningId === null) {
                $this->fail('Pilih rekening utama terlebih dahulu sebelum mengisi sub rekening.');
            }

            if ($subRekening !== null && (int) $subRekening->parent_id !== (int) $rekeningId) {
                $this->fail('Sub rekening harus merupakan detail dari rekening utama yang dipilih.');
            }

            if ($subRekening !== null && $subRekening->tipe !== 'pendapatan') {
                $this->fail('Sub rekening penerimaan harus bertipe pendapatan.');
            }
        }

        return [
            'opd_id' => $this->resolveOpd($this->requiredString($row, 'OPD', 'OPD')),
            'rekening_id' => $rekeningId,
            'sub_rekening_id' => $subRekeningId,
            'tahun_anggaran_id' => $this->resolveTahun($this->optionalString($row, 'Tahun Anggaran (opsional)')),
            'target' => $target,
        ];
    }

    public function importRow(array $row): string
    {
        $penerimaan = Penerimaan::updateOrCreate(
            [
                'opd_id' => $row['opd_id'],
                'rekening_id' => $row['rekening_id'],
                'sub_rekening_id' => $row['sub_rekening_id'],
                'tahun_anggaran_id' => $row['tahun_anggaran_id'],
            ],
            ['target' => $row['target']]
        );

        return $penerimaan->wasRecentlyCreated ? 'created' : 'updated';
    }
}
