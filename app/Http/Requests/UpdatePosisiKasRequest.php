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
            'tanggal' => ['nullable', 'date'],
            'nama_rekening' => ['required', 'string', 'max:255'],
            'nomor_rekening' => ['nullable', 'string', 'max:100'],
            'saldo' => ['required', 'numeric'],
        ];
    }
}
