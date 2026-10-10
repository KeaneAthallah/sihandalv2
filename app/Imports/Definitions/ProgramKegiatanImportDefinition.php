<?php

namespace App\Imports\Definitions;

use App\Imports\MasterImportDefinition;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;

class ProgramKegiatanImportDefinition extends MasterImportDefinition
{
    public function label(): string
    {
        return 'Program & Kegiatan';
    }

    public function filename(): string
    {
        return 'template-program-kegiatan.xlsx';
    }

    public function headers(): array
    {
        return [
            'Kode Program',
            'Nama Program',
            'Kode Kegiatan',
            'Nama Kegiatan',
            'OPD',
            'Sumber Dana (opsional)',
            'Kode Rekening (opsional)',
            'Tahun Anggaran (opsional)',
            'Pagu',
            'Realisasi (opsional)',
        ];
    }

    public function exampleRow(): array
    {
        return [
            'Kode Program' => '1.2',
            'Nama Program' => 'Program Contoh',
            'Kode Kegiatan' => '1.2.3',
            'Nama Kegiatan' => 'Kegiatan Contoh',
            'OPD' => 'Dinas Pendidikan',
            'Sumber Dana (opsional)' => 'Dana Alokasi Umum (DAU)',
            'Kode Rekening (opsional)' => '5.2.1',
            'Tahun Anggaran (opsional)' => '2026',
            'Pagu' => '500000000',
            'Realisasi (opsional)' => '100000000',
        ];
    }

    public function referenceData(): array
    {
        return [
            'OPD' => Opd::orderBy('nama')->get()->map(fn ($opd) => [
                'Kode' => $opd->kode,
                'Nama' => $opd->nama,
            ])->all(),
            'Sumber Dana' => SumberDana::orderBy('nama_sumber_dana')->get()->map(fn ($sumberDana) => [
                'Nama' => $sumberDana->nama_sumber_dana,
            ])->all(),
            'Rekening' => Rekening::orderBy('kode')->get()->map(fn ($rekening) => [
                'Kode' => $rekening->kode,
                'Nama' => $rekening->nama,
            ])->all(),
            'Tahun Anggaran' => TahunAnggaran::orderByDesc('tahun')->get()->map(fn ($tahunAnggaran) => [
                'Tahun' => $tahunAnggaran->tahun,
            ])->all(),
        ];
    }

    public function dropdowns(): array
    {
        return [
            'OPD' => ['type' => 'reference', 'reference' => 'OPD'],
            'Sumber Dana (opsional)' => ['type' => 'reference', 'reference' => 'Sumber Dana'],
            'Kode Rekening (opsional)' => ['type' => 'reference', 'reference' => 'Rekening'],
            'Tahun Anggaran (opsional)' => ['type' => 'reference', 'reference' => 'Tahun Anggaran'],
        ];
    }

    public function parseRow(array $row): array
    {
        $pagu = $this->requiredNumeric($row, 'Pagu', 'Pagu');
        $realisasi = $this->optionalNumeric($row, 'Realisasi (opsional)');

        return [
            'kode_program' => $this->requiredString($row, 'Kode Program', 'Kode Program'),
            'nama_program' => $this->requiredString($row, 'Nama Program', 'Nama Program'),
            'kode_kegiatan' => $this->requiredString($row, 'Kode Kegiatan', 'Kode Kegiatan'),
            'nama_kegiatan' => $this->requiredString($row, 'Nama Kegiatan', 'Nama Kegiatan'),
            'opd_id' => $this->resolveOpd($this->requiredString($row, 'OPD', 'OPD')),
            'sumber_dana_id' => $this->resolveSumberDana($this->optionalString($row, 'Sumber Dana (opsional)')),
            'rekening_id' => $this->resolveRekeningByKode($this->optionalString($row, 'Kode Rekening (opsional)')),
            'tahun_anggaran_id' => $this->resolveTahun($this->optionalString($row, 'Tahun Anggaran (opsional)')),
            'pagu' => $pagu,
            'realisasi' => $realisasi,
            'persentase' => $pagu > 0 ? round($realisasi / $pagu * 100, 2) : 0,
        ];
    }

    public function importRow(array $row): string
    {
        // Programs are global: a new program inherits the row's OPD,
        // an existing one keeps its original OPD and only refreshes
        // its name.
        $program = Program::where('kode_program', $row['kode_program'])->first();

        if ($program === null) {
            $program = Program::create([
                'kode_program' => $row['kode_program'],
                'nama_program' => $row['nama_program'],
                'opd_id' => $row['opd_id'],
            ]);
        } else {
            $program->update(['nama_program' => $row['nama_program']]);
        }

        $kegiatan = Kegiatan::updateOrCreate(
            [
                'program_id' => $program->id,
                'kode_kegiatan' => $row['kode_kegiatan'],
                'opd_id' => $row['opd_id'],
            ],
            [
                'nama_kegiatan' => $row['nama_kegiatan'],
                'sumber_dana_id' => $row['sumber_dana_id'],
                'rekening_id' => $row['rekening_id'],
                'tahun_anggaran_id' => $row['tahun_anggaran_id'],
                'pagu' => $row['pagu'],
                'realisasi' => $row['realisasi'],
                'persentase' => $row['persentase'],
            ]
        );

        return $kegiatan->wasRecentlyCreated ? 'created' : 'updated';
    }
}
