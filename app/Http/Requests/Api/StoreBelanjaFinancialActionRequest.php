<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the explicit Belanja financial actions: commit, release and
 * realize. The amount must be positive; all financial invariants are enforced
 * by the Belanja domain methods, never here.
 */
class StoreBelanjaFinancialActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
