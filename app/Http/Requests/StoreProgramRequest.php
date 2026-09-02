<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_program' => ['required', 'string', 'max:50', 'unique:programs,kode_program'],
            'nama_program' => ['required', 'string', 'max:255'],
            'opd_id' => ['nullable', 'exists:opds,id'],
            'tahun_anggaran_id' => ['nullable', 'exists:tahun_anggarans,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $opdId = $this->input('opd_id');

            if (! $user->isAdmin() && $opdId !== null && (int) $opdId !== (int) $user->opd_id) {
                $validator->errors()->add('opd_id', 'Anda hanya dapat mengelola program untuk OPD Anda sendiri.');
            }
        });
    }
}
