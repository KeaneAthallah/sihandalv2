<?php

namespace App\Http\Requests\Api;

use App\Models\Rekening;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API variant of the PosisiKas request. Only a kas LEAF rekening may be
 * selected: a kas parent with children is rejected.
 */
class StorePosisiKasApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'rekening_id' => ['required', 'integer', Rule::exists('rekenings', 'id')->where(fn (Builder $q) => $q->where('tipe', 'kas'))],
            'tanggal' => ['nullable', 'date'],
            'saldo_awal' => ['required', 'numeric'],
            'penerimaan' => ['nullable', 'numeric', 'min:0'],
            'pengeluaran' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $rekening = Rekening::withCount('children')->find($this->input('rekening_id'));
            if ($rekening && $rekening->children_count > 0) {
                $validator->errors()->add('rekening_id', 'Rekening kas induk tidak dapat dipilih. Pilih rekening kas pada level detail (anak) atau rekening kas tanpa detail.');
            }
        });
    }
}
