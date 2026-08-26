<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengeluaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'rekening_id' => ['nullable', 'exists:rekenings,id'],
            'kode_kegiatan' => ['nullable', 'string', 'max:50'],
            'nama_kegiatan' => ['nullable', 'string', 'max:255'],
            'sumber_dana' => ['required', 'string', 'max:255'],
            'anggaran' => ['required', 'numeric', 'min:0'],
            'realisasi' => ['nullable', 'numeric', 'min:0'],
            'tanggal' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
