<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API variant of the RekeningBank store request. Account numbers must be
 * globally unique (RekeningBank is a global master).
 */
class StoreRekeningBankApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100', Rule::unique('rekening_banks', 'account_number')],
            'account_name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.unique' => 'Nomor rekening bank sudah digunakan.',
        ];
    }
}
