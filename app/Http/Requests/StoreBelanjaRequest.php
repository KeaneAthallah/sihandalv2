<?php

namespace App\Http\Requests;

use App\Models\SubKegiatan;
use Illuminate\Foundation\Http\FormRequest;

class StoreBelanjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sub_kegiatan_id' => ['required', 'exists:sub_kegiatans,id'],
            'rekening_id' => ['required', 'exists:rekenings,id'],
            'sumber_dana_id' => ['nullable', 'exists:sumber_danas,id'],
            'opd_id' => ['required', 'exists:opds,id'],
            'pagu' => ['required', 'numeric', 'min:0'],
            'realisasi' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $opdId = $this->input('opd_id');

            if (! $user->isAdmin() && (int) $opdId !== (int) $user->opd_id) {
                $validator->errors()->add('opd_id', 'Anda hanya dapat mengelola belanja untuk OPD Anda sendiri.');
            }

            $subKegiatan = SubKegiatan::with('kegiatan')->find($this->input('sub_kegiatan_id'));
            if ($subKegiatan && (int) $subKegiatan->kegiatan?->opd_id !== (int) $opdId) {
                $validator->errors()->add('sub_kegiatan_id', 'Sub kegiatan tidak sesuai dengan OPD yang dipilih.');
            }
        });
    }
}
