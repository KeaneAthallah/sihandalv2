<?php

namespace App\Http\Requests\Concerns;

use App\Models\SumberDana;
use Illuminate\Validation\Validator;

trait ValidatesPermintaanDana
{
    protected function permintaanDanaRules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'sumber_dana_id' => ['required', 'exists:sumber_danas,id'],
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'keperluan' => ['required', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    protected function validatePermintaanDana(Validator $validator): void
    {
        $user = $this->user();
        $sumberDana = SumberDana::find($this->input('sumber_dana_id'));
        $opdId = $this->input('opd_id');
        $jumlah = (float) $this->input('jumlah', 0);

        if ($sumberDana !== null) {
            if ((int) $opdId !== (int) $sumberDana->opd_id) {
                $validator->errors()->add('sumber_dana_id', 'Sumber dana tidak tersedia untuk OPD yang dipilih.');
            }

            $available = $sumberDana->availablePagu();
            if ($jumlah > $available) {
                $validator->errors()->add('jumlah', 'Jumlah permintaan melebihi sisa pagu sumber dana (sisa tersedia: Rp '.number_format($available, 0, ',', '.').').');
            }
        }

        if (! $user->isAdmin() && (int) $opdId !== (int) $user->opd_id) {
            $validator->errors()->add('opd_id', 'Anda hanya dapat membuat permintaan untuk OPD Anda sendiri.');
        }
    }
}
