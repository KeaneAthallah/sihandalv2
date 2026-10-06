<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTransferDanaRequest extends FormRequest
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
            'sumber_dana_pengirim_id' => ['required', 'integer', 'exists:sumber_danas,id'],
            'sumber_dana_penerima_id' => ['required', 'integer', 'exists:sumber_danas,id', 'different:sumber_dana_pengirim_id'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('sumber_dana_pengirim_id') !== null
                && $this->input('sumber_dana_pengirim_id') === $this->input('sumber_dana_penerima_id')) {
                $validator->errors()->add('sumber_dana_penerima_id', 'Sumber dana pengirim dan penerima tidak boleh sama.');
            }
        });
    }
}
