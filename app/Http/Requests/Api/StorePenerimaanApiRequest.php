<?php

namespace App\Http\Requests\Api;

use App\Models\Penerimaan;
use App\Models\Rekening;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API variant of the Penerimaan master request, mirroring the web
 * rules: rekening must be tipe pendapatan, OPD ownership, and a sub
 * rekening must be a detail of the chosen rekening utama.
 */
class StorePenerimaanApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'rekening_id' => ['nullable', Rule::exists('rekenings', 'id')->where(fn (Builder $q) => $q->where('tipe', 'pendapatan'))],
            'sub_rekening_id' => ['nullable', 'integer', 'exists:rekenings,id'],
            'tahun_anggaran_id' => ['nullable', 'exists:tahun_anggarans,id'],
            'target' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();
            $opdId = $this->input('opd_id');

            if (! $user->isAdmin() && (int) $opdId !== (int) $user->opd_id) {
                $validator->errors()->add('opd_id', 'Anda hanya dapat mengelola penerimaan untuk OPD Anda sendiri.');
            }

            if ($validator->errors()->has('rekening_id')) {
                $validator->errors()->forget('rekening_id');
                $validator->errors()->add('rekening_id', 'Rekening penerimaan harus bertipe pendapatan.');
            }

            $rekeningId = $this->input('rekening_id');
            $subRekeningId = $this->input('sub_rekening_id');

            if ($subRekeningId === null || $subRekeningId === '') {
                return;
            }

            $sub = Rekening::find($subRekeningId);
            if ($sub === null) {
                return;
            }

            if ($rekeningId === null || $rekeningId === '') {
                $validator->errors()->add('sub_rekening_id', 'Pilih rekening utama terlebih dahulu sebelum memilih sub rekening.');

                return;
            }

            if ((int) $sub->parent_id !== (int) $rekeningId) {
                $validator->errors()->add('sub_rekening_id', 'Sub rekening harus merupakan detail dari rekening utama yang dipilih.');
            }

            if ($sub->tipe !== 'pendapatan') {
                $validator->errors()->add('sub_rekening_id', 'Sub rekening penerimaan harus bertipe pendapatan.');
            }
        });
    }
}
