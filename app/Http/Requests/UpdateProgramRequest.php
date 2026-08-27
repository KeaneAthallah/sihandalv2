<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $programId = $this->route('program')->id;

        return [
            'kode_program' => ['required', 'string', 'max:50', 'unique:programs,kode_program,'.$programId],
            'nama_program' => ['required', 'string', 'max:255'],
        ];
    }
}
