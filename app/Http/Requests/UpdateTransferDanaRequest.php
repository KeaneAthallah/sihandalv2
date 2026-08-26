<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransferDanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'sumber_dana' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,diproses,selesai,gagal'],
        ];
    }
}
