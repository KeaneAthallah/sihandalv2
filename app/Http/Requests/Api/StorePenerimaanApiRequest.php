<?php

namespace App\Http\Requests\Api;

use App\Models\Penerimaan;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API variant of the Penerimaan master request, mirroring the web rules:
 *   - rekening must be tipe pendapatan
 *   - OPD ownership
 *   - nested details must reference the master and use unique sumber dana
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
            'sumber_dana_id' => ['nullable', 'exists:sumber_danas,id'],
            'kode_sumber_dana' => ['nullable', 'string', 'max:50'],
            'nama_sumber_dana' => ['nullable', 'string', 'max:255'],
            'target' => ['required', 'numeric', 'min:0'],
            'details' => ['sometimes', 'array'],
            'details.*.id' => ['sometimes', 'nullable', 'integer'],
            'details.*.sumber_dana_id' => ['required_with:details', 'integer', 'exists:sumber_danas,id'],
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

            $penerimaan = $this->route('penerimaan') instanceof Penerimaan
                ? $this->route('penerimaan')
                : null;

            $ownDetailIds = $penerimaan ? $penerimaan->details()->pluck('id')->all() : [];

            $seenSumber = [];
            foreach ($this->input('details', []) as $index => $row) {
                if (! empty($row['id'] ?? null) && ! in_array((int) $row['id'], $ownDetailIds, true)) {
                    $validator->errors()->add("details.$index.id", 'Detail tidak sesuai dengan penerimaan yang dipilih.');
                }

                $sumberId = (int) ($row['sumber_dana_id'] ?? 0);

                if ($sumberId === 0) {
                    continue;
                }

                if (in_array($sumberId, $seenSumber, true)) {
                    $validator->errors()->add("details.$index.sumber_dana_id", 'Kombinasi sumber dana ganda pada satu penerimaan tidak diperbolehkan.');
                }

                $seenSumber[] = $sumberId;
            }
        });
    }
}
