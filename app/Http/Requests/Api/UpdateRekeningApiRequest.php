<?php

namespace App\Http\Requests\Api;

use App\Models\Rekening;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRekeningApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:50', Rule::unique('rekenings', 'kode')->ignore($this->route('rekening'))],
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'string', 'in:kas,non-kas,pendapatan,belanja'],
            'parent_id' => ['nullable', 'integer', 'exists:rekenings,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $rekening = $this->route('rekening');
            $parentId = $this->input('parent_id');
            $tipe = $this->input('tipe');

            if ($rekening instanceof Rekening && $parentId !== null) {
                if ((int) $parentId === (int) $rekening->id) {
                    $validator->errors()->add('parent_id', 'Rekening tidak dapat menjadi induk bagi dirinya sendiri.');

                    return;
                }
                $excludedIds = collect([$rekening->id])->merge($this->descendantIds($rekening))->all();

                if (in_array((int) $parentId, $excludedIds, true)) {
                    $validator->errors()->add('parent_id', 'Hierarki rekening kas tidak boleh membentuk lingkaran (circular).');

                    return;
                }
            }

            if ($parentId === null) {
                return;
            }

            $parent = Rekening::find($parentId);
            if ($parent === null) {
                return;
            }

            if ($parent->tipe !== $tipe) {
                $validator->errors()->add('parent_id', 'Rekening detail harus bertipe sama dengan rekening induknya.');
            }
        });
    }

    /**
     * All descendant ids of the rekening (BFS over children).
     *
     * @return array<int, int>
     */
    private function descendantIds(Rekening $rekening): array
    {
        $ids = [];
        $queue = [$rekening->id];

        while ($queue !== []) {
            $id = array_shift($queue);
            $children = Rekening::where('parent_id', $id)->pluck('id')->all();
            $ids = array_merge($ids, $children);
            $queue = array_merge($queue, $children);
        }

        return array_map(fn ($id) => (int) $id, $ids);
    }
}
