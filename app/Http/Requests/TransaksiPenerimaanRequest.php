<?php

namespace App\Http\Requests;

use App\Models\Penerimaan;
use App\Models\Rekening;
use Illuminate\Foundation\Http\FormRequest;

abstract class TransaksiPenerimaanRequest extends FormRequest
{
    /**
     * Rules shared by create and update of a transaksi penerimaan transaction,
     * including the nested BKU details.
     */
    public function rules(): array
    {
        return [
            'penerimaan_id' => ['required', 'exists:penerimaans,id'],
            'realisasi' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'bkus' => ['nullable', 'array'],
            'bkus.*.id' => ['sometimes', 'nullable', 'integer', 'exists:transaksi_penerimaan_bkus,id'],
            'bkus.*.nomor_bku' => ['required', 'string', 'max:100'],
            'bkus.*.tanggal_bku' => ['required', 'date'],
            'bkus.*.nilai' => ['required', 'numeric', 'min:0'],
            'bkus.*.rekening_id' => ['required', 'integer', 'exists:rekenings,id'],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $penerimaanId = $this->input('penerimaan_id');

            if ($penerimaanId === null) {
                return;
            }

            $master = Penerimaan::find($penerimaanId);
            if ($master === null) {
                return;
            }

            // Province-wide masters (opd_id = null) are admin-managed only.
            if ($master->opd_id === null && ! $user->isAdmin()) {
                $validator->errors()->add('penerimaan_id', 'Transaksi tidak dapat ditambahkan ke Penerimaan ini.');
            }

            if (! $user->isAdmin() && $master->opd_id !== null && (int) $master->opd_id !== (int) $user->opd_id) {
                $validator->errors()->add('penerimaan_id', 'Anda hanya dapat mengelola transaksi untuk Penerimaan OPD Anda sendiri.');
            }

            $this->validatePendapatanRekening($validator);
            $this->validateBkuSumEqualsRealisasi($validator);
        });
    }

    /**
     * BKU rekening must reference a valid "pendapatan" rekening, matching the
     * business rule used on the Penerimaan master.
     */
    private function validatePendapatanRekening($validator): void
    {
        $bkus = $this->input('bkus');

        if (! is_array($bkus)) {
            return;
        }

        foreach ($bkus as $index => $bku) {
            $rekeningId = $bku['rekening_id'] ?? null;

            if ($rekeningId === null) {
                continue;
            }

            $rekening = Rekening::find($rekeningId);
            if ($rekening === null) {
                continue;
            }

            if ($rekening->tipe !== 'pendapatan') {
                $validator->errors()->add(
                    "bkus.{$index}.rekening_id",
                    'Rekening BKU harus bertipe pendapatan.'
                );
            }
        }
    }

    /**
     * The sum of all BKU details must exactly match the transaction realisasi,
     * but only when at least one BKU row exists. A transaction with zero BKU is
     * valid regardless of realisasi (BKU may be completed later).
     */
    private function validateBkuSumEqualsRealisasi($validator): void
    {
        $bkus = $this->input('bkus');
        $realisasi = $this->input('realisasi');

        // No BKU rows (absent, null, or empty) => rule does not apply.
        if (! is_array($bkus) || $bkus === [] || $realisasi === null) {
            return;
        }

        $total = 0;
        foreach ($bkus as $bku) {
            $nilai = (float) ($bku['nilai'] ?? 0);
            $total += $nilai;
        }

        // Compare with a small epsilon to avoid silent rounding discrepancies.
        if (abs($total - (float) $realisasi) > 0.0001) {
            $validator->errors()->add(
                'bkus',
                'Total nilai BKU harus sama dengan nilai transaksi penerimaan.'
            );
        }
    }
}
