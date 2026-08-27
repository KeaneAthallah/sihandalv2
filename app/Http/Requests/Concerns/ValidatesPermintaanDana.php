<?php

namespace App\Http\Requests\Concerns;

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
        $opdId = $this->input('opd_id');

        if (! $user->isAdmin() && (int) $opdId !== (int) $user->opd_id) {
            $validator->errors()->add('opd_id', 'Anda hanya dapat membuat permintaan untuk OPD Anda sendiri.');
        }
    }
}
