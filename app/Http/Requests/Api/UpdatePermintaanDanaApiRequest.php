<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\ValidatesPermintaanDana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePermintaanDanaApiRequest extends FormRequest
{
    use ValidatesPermintaanDana;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->permintaanDanaRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validatePermintaanDana($validator);
        });
    }
}
