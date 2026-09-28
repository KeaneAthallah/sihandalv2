<?php

namespace App\Http\Requests\Api;

use App\Models\Rekening;
use Illuminate\Foundation\Http\FormRequest;

/**
 * API variant of the Rekening (kas/pendapatan/belanja master) request with the
 * same kas hierarchy rules as the web form: only kas accounts may host kas
 * detail accounts, and detail accounts are always kas.
 */
class StoreRekeningApiRequest extends FormRequest
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
        $validator->after(function ($validator): void {
            $parentId = $this->input('parent_id');
            $tipe = $this->input('tipe');

            if ($parentId === null) {
                return;
            }

            $parent = Rekening::find($parentId);
            if ($parent === null) {
                return;
            }

            if ($parent->tipe !== 'kas') {
                $validator->errors()->add('parent_id', 'Hanya rekening bertipe kas yang dapat memiliki rekening detail kas.');
            }

            if ($tipe !== 'kas') {
                $validator->errors()->add('tipe', 'Rekening detail kas harus bertipe kas.');
            }
        });
    }
}
