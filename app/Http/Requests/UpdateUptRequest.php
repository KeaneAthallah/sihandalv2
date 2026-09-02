<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uptId = $this->route('upt')->id;

        return [
            'kode' => ['required', 'string', 'max:50', 'unique:upts,kode,'.$uptId],
            'nama' => ['required', 'string', 'max:255'],
            'opd_id' => ['required', 'exists:opds,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $opdId = $this->input('opd_id');

            if (! $user->isAdmin() && (int) $opdId !== (int) $user->opd_id) {
                $validator->errors()->add('opd_id', 'Anda hanya dapat mengelola UPT untuk OPD Anda sendiri.');
            }
        });
    }
}
