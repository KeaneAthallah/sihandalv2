<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * API variant of the TransferDana update request. Status transitions are
 * whitelisted; the controller further guards that selesai is final.
 */
class UpdateTransferDanaApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'sumber_dana' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,diproses,selesai,gagal'],
        ];
    }
}
