<?php

namespace App\Http\Requests;

use App\Models\Penerimaan;
use App\Models\Rekening;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class PenerimaanRequest extends FormRequest
{
    /**
     * Rules shared by create and update of a Penerimaan master.
     * The master is keyed by rekening (utama + sub), not by
     * sumber dana.
     */
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
        $validator->after(function ($validator) {
            $user = $this->user();
            $opdId = $this->input('opd_id');

            if (! $user->isAdmin() && (int) $opdId !== (int) $user->opd_id) {
                $validator->errors()->add('opd_id', 'Anda hanya dapat mengelola penerimaan untuk OPD Anda sendiri.');
            }

            if ($validator->errors()->has('rekening_id')) {
                $validator->errors()->forget('rekening_id');
                $validator->errors()->add('rekening_id', 'Rekening penerimaan harus bertipe pendapatan.');
            }

            $this->validateSubRekening($validator);
        });
    }

    /**
     * A sub rekening must be a detail of the chosen rekening utama
     * and share its pendapatan tipe.
     */
    private function validateSubRekening($validator): void
    {
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
    }
}
