<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSumberDanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'nama_sumber_dana' => ['required', 'string', 'max:255'],
            'pagu' => ['required', 'numeric', 'min:0'],
            'realisasi' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
