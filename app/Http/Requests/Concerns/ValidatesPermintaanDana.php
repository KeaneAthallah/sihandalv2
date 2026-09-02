<?php

namespace App\Http\Requests\Concerns;

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Illuminate\Validation\Validator;

trait ValidatesPermintaanDana
{
    protected function permintaanDanaRules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'sumber_dana_id' => ['required', 'exists:sumber_danas,id'],
            'kegiatan_id' => ['nullable', 'exists:kegiatan,id'],
            'sub_kegiatan_id' => ['nullable', 'exists:sub_kegiatans,id'],
            'belanja_id' => ['nullable', 'exists:belanjas,id'],
            'rekening_id' => ['nullable', 'exists:rekenings,id'],
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'keperluan' => ['required', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    protected function validatePermintaanDana(Validator $validator): void
    {
        $user = $this->user();
        $opdId = $this->input('opd_id');

        if (! $user->isAdmin() && (int) $opdId !== (int) $user->opd_id) {
            $validator->errors()->add('opd_id', 'Anda hanya dapat membuat permintaan untuk OPD Anda sendiri.');
        }

        $kegiatanId = $this->input('kegiatan_id');
        if ($kegiatanId) {
            $kegiatan = Kegiatan::find($kegiatanId);
            if ($kegiatan && (int) $kegiatan->opd_id !== (int) $opdId) {
                $validator->errors()->add('kegiatan_id', 'Kegiatan tidak sesuai dengan OPD yang dipilih.');
            }
        }

        $subKegiatanId = $this->input('sub_kegiatan_id');
        if ($subKegiatanId) {
            $subKegiatan = SubKegiatan::find($subKegiatanId);
            if ($subKegiatan && $subKegiatan->kegiatan_id !== (int) $kegiatanId) {
                $validator->errors()->add('sub_kegiatan_id', 'Sub kegiatan tidak sesuai dengan kegiatan yang dipilih.');
            }
        }

        $belanjaId = $this->input('belanja_id');
        if ($belanjaId) {
            $belanja = Belanja::find($belanjaId);
            if ($belanja && $belanja->sub_kegiatan_id !== (int) $subKegiatanId) {
                $validator->errors()->add('belanja_id', 'Belanja tidak sesuai dengan sub kegiatan yang dipilih.');
            }
        }
    }
}
