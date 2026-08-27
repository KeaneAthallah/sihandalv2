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
            'kode_program' => ['required', 'string', 'max:50', 'unique:programs,kode_program'],
            'nama_program' => ['required', 'string', 'max:255'],
        ];
    }
}
