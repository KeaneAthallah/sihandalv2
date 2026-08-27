<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSumberDanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_sumber_dana' => ['required', 'string', 'max:255', 'unique:sumber_danas,nama_sumber_dana'],
        ];
    }
}
