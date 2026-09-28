<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreKegiatanApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'sumber_dana_id' => ['nullable', 'exists:sumber_danas,id'],
            'rekening_id' => ['nullable', 'exists:rekenings,id'],
            'tahun_anggaran_id' => ['nullable', 'exists:tahun_anggarans,id'],
            'kode_kegiatan' => ['required', 'string', 'max:50'],
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'pagu' => ['required', 'numeric', 'min:0'],
            'realisasi' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();
            $opdId = $this->input('opd_id');

            if (! $user->isAdmin() && (int) $opdId !== (int) $user->opd_id) {
                $validator->errors()->add('opd_id', 'Anda hanya dapat mengelola kegiatan untuk OPD Anda sendiri.');
            }
        });
    }
}
