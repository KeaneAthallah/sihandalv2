<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePosisiKasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'rekening_id' => ['required', 'exists:rekenings,id'],
            'tanggal' => ['nullable', 'date'],
            'saldo_awal' => ['required', 'numeric'],
            'penerimaan' => ['nullable', 'numeric', 'min:0'],
            'pengeluaran' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
