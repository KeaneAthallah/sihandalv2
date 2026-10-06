<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\SubKegiatan;

/**
 * Cascade option maps for the OPD -> program -> kegiatan ->
 * sub kegiatan -> belanja hierarchy used by the Permintaan
 * Dana and Pengeluaran forms.
 */
trait ProvidesBudgetHierarchyCascade
{
    /**
     * Programs grouped by OPD.
     *
     * @return array<string, array<int, array{id: string, label: string}>>
     */
    protected function programsByOpd(): array
    {
        return Program::orderBy('kode_program')
            ->get(['id', 'opd_id', 'kode_program', 'nama_program'])
            ->groupBy('opd_id')
            ->map(fn ($rows) => $rows->map(fn ($p) => [
                'id' => (string) $p->id,
                'label' => $p->kode_program.' - '.$p->nama_program,
            ])->values())
            ->all();
    }

    /**
     * Kegiatans grouped by program.
     *
     * @return array<string, array<int, array{id: string, label: string}>>
     */
    protected function kegiatansByProgram($user): array
    {
        $query = Kegiatan::orderBy('kode_kegiatan');

        if (! $user->isAdmin()) {
            $query->where('opd_id', $user->opd_id);
        }

        return $query->get(['id', 'program_id', 'kode_kegiatan', 'nama_kegiatan'])
            ->groupBy('program_id')
            ->map(fn ($rows) => $rows->map(fn ($k) => [
                'id' => (string) $k->id,
                'label' => $k->kode_kegiatan.' - '.$k->nama_kegiatan,
            ])->values())
            ->all();
    }

    /**
     * Sub kegiatans grouped by kegiatan.
     *
     * @return array<string, array<int, array{id: string, label: string}>>
     */
    protected function subKegiatansByKegiatan($user): array
    {
        $query = SubKegiatan::orderBy('kode_sub_kegiatan');

        if (! $user->isAdmin()) {
            $query->whereHas('kegiatan', fn ($q) => $q->where('opd_id', $user->opd_id));
        }

        return $query->get(['id', 'kegiatan_id', 'kode_sub_kegiatan', 'nama_sub_kegiatan'])
            ->groupBy('kegiatan_id')
            ->map(fn ($rows) => $rows->map(fn ($s) => [
                'id' => (string) $s->id,
                'label' => $s->kode_sub_kegiatan.' - '.$s->nama_sub_kegiatan,
            ])->values())
            ->all();
    }

    /**
     * Belanjas grouped by sub kegiatan, labelled by their rekening.
     *
     * @return array<string, array<int, array{id: string, label: string}>>
     */
    protected function belanjasBySubKegiatan($user): array
    {
        $query = Belanja::with('rekening')->orderBy('id');

        if (! $user->isAdmin()) {
            $query->where('opd_id', $user->opd_id);
        }

        return $query->get(['id', 'sub_kegiatan_id', 'rekening_id'])
            ->groupBy('sub_kegiatan_id')
            ->map(fn ($rows) => $rows->map(function ($b) {
                $rekening = $b->rekening;

                return [
                    'id' => (string) $b->id,
                    'label' => $rekening
                        ? $rekening->kode.' - '.$rekening->nama
                        : 'Belanja #'.$b->id,
                ];
            })->values())
            ->all();
    }
}
