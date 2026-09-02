<?php

namespace App\Http\Requests;

use App\Models\Penerimaan;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransaksiPenerimaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'penerimaan_id' => ['required', 'exists:penerimaans,id'],
            'realisasi' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $penerimaanId = $this->input('penerimaan_id');

            if ($penerimaanId === null) {
                return;
            }

            $master = Penerimaan::find($penerimaanId);
            if ($master === null) {
                return;
            }

            // Province-wide masters (opd_id = null) are admin-managed only.
            if ($master->opd_id === null && ! $user->isAdmin()) {
                $validator->errors()->add('penerimaan_id', 'Transaksi tidak dapat ditambahkan ke Penerimaan ini.');
            }

            if (! $user->isAdmin() && $master->opd_id !== null && (int) $master->opd_id !== (int) $user->opd_id) {
                $validator->errors()->add('penerimaan_id', 'Anda hanya dapat mencatat transaksi untuk Penerimaan OPD Anda sendiri.');
            }
        });
    }
}
