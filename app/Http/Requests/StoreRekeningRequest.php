<?php

namespace App\Http\Requests;

use App\Models\Rekening;
use Illuminate\Foundation\Http\FormRequest;

class StoreRekeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:50', 'unique:rekenings,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'string', 'in:kas,non-kas,pendapatan,belanja'],
            'parent_id' => ['nullable', 'integer', 'exists:rekenings,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_id');
            $tipe = $this->input('tipe');

            if ($parentId === null) {
                return;
            }

            $parent = Rekening::find($parentId);
            if ($parent === null) {
                return;
            }

            // A sub rekening (detail) must share its induk's tipe.
            if ($parent->tipe !== $tipe) {
                $validator->errors()->add('parent_id', 'Rekening detail harus bertipe sama dengan rekening induknya.');
            }
        });
    }
}
