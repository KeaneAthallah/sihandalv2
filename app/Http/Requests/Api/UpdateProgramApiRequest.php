<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgramApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_program' => ['required', 'string', 'max:50', Rule::unique('programs', 'kode_program')->ignore($this->route('program'))],
            'nama_program' => ['required', 'string', 'max:255'],
            'opd_id' => ['nullable', 'exists:opds,id'],
            'tahun_anggaran_id' => ['nullable', 'exists:tahun_anggarans,id'],
        ];
    }
}
