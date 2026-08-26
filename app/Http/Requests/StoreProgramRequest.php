<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'kode_kegiatan' => ['required', 'string', 'max:50'],
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'kode_sub_kegiatan' => ['nullable', 'string', 'max:50'],
            'nama_sub_kegiatan' => ['nullable', 'string', 'max:255'],
            'kode_rekening' => ['nullable', 'string', 'max:50'],
            'nama_rekening' => ['nullable', 'string', 'max:255'],
            'sumber_dana' => ['required', 'string', 'max:255'],
            'pagu' => ['required', 'numeric', 'min:0'],
            'realisasi' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
