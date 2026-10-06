<?php

namespace App\Http\Requests;

use App\Models\Rekening;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRekeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:50', 'unique:rekenings,kode,'.$this->route('rekening')?->id],
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'string', 'in:kas,non-kas,pendapatan,belanja'],
            'parent_id' => ['nullable', 'integer', 'exists:rekenings,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $current = $this->route('rekening');
            $parentId = $this->input('parent_id');
            $tipe = $this->input('tipe');

            // An induk that already hosts detail accounts cannot change tipe.
            if ($current && $current->children()->exists() && $tipe !== $current->tipe) {
                $validator->errors()->add('tipe', 'Rekening induk tidak dapat diubah tipenya karena masih memiliki detail.');
            }

            if ($parentId === null) {
                return;
            }

            if ($current && (int) $parentId === (int) $current->id) {
                $validator->errors()->add('parent_id', 'Rekening tidak dapat menjadi detail dari dirinya sendiri.');

                return;
            }

            $parent = Rekening::find($parentId);
            if ($parent === null) {
                return;
            }

            if ($parent->tipe !== $tipe) {
                $validator->errors()->add('parent_id', 'Rekening detail harus bertipe sama dengan rekening induknya.');
            }

            if ($current) {
                // Walk up the ancestors of the intended parent. Reaching the
                // current rekening means assigning it as parent creates a cycle.
                $ancestor = $parent;
                while ($ancestor !== null) {
                    if ((int) $ancestor->id === (int) $current->id) {
                        $validator->errors()->add('parent_id', 'Hierarki rekening tidak boleh melingkar.');

                        return;
                    }

                    $ancestor = $ancestor->parent;
                }
            }
        });
    }
}
