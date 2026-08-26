<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRekeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:50', 'unique:rekenings,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'string', 'in:kas,non-kas,pendapatan,belanja'],
            'saldo' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
