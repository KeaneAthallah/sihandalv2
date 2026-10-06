<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * API variant of the PosisiKas store request. Posisi Kas is a
 * standalone record: it names a rekening directly (nama + nomor)
 * and carries a single saldo.
 */
class StorePosisiKasApiRequest extends FormRequest
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
