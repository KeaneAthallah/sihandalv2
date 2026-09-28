<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\ValidatesPermintaanDana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * API variant of the PermintaanDana request. Reuses the exact shared
 * ValidatesPermintaanDana concern from the web form requests so hierarchy and
 * OPD rules live in one place. status is never client-settable.
 */
class StorePermintaanDanaApiRequest extends FormRequest
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
