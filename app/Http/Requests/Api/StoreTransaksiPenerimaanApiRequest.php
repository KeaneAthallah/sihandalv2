<?php

namespace App\Http\Requests\Api;

use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use App\Models\RekeningBank;
use Illuminate\Foundation\Http\FormRequest;

/**
 * API variant of the transaksi penerimaan request. Mirrors the web
 * TransaksiPenerimaanRequest rules including nested BKU validation:
 *   - OPD ownership of the selected Penerimaan master
 *   - penerimaan_detail_id must belong to the master
 *   - every booked Rekening Bank must be active
 *   - SUM(BKU nilai) must equal realisasi when BKU rows exist
 * nomor_registrasi is NOT accepted from clients: it is generated server-side.
 */
class StoreTransaksiPenerimaanApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'penerimaan_id' => ['required', 'exists:penerimaans,id'],
            'penerimaan_detail_id' => ['nullable', 'integer', 'exists:penerimaan_details,id'],
            'realisasi' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'bkus' => ['nullable', 'array'],
            'bkus.*.id' => ['sometimes', 'nullable', 'integer', 'exists:transaksi_penerimaan_bkus,id'],
            'bkus.*.nomor_bku' => ['required_with:bkus', 'string', 'max:100'],
            'bkus.*.tanggal_bku' => ['required_with:bkus', 'date'],
            'bkus.*.nilai' => ['required_with:bkus', 'numeric', 'min:0'],
            'bkus.*.rekening_bank_id' => ['nullable', 'integer', 'exists:rekening_banks,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();
            $penerimaanId = $this->input('penerimaan_id');

            if ($penerimaanId === null) {
                return;
            }

            $master = Penerimaan::find($penerimaanId);
            if ($master === null) {
                return;
            }

            if ($master->opd_id === null && ! $user->isAdmin()) {
                $validator->errors()->add('penerimaan_id', 'Transaksi tidak dapat ditambahkan ke Penerimaan ini.');
            }

            if (! $user->isAdmin() && $master->opd_id !== null && (int) $master->opd_id !== (int) $user->opd_id) {
                $validator->errors()->add('penerimaan_id', 'Anda hanya dapat mengelola transaksi untuk Penerimaan OPD Anda sendiri.');
            }

            $this->validateDetailBelongsToPenerimaan($validator);
            $this->validateActiveRekeningBanks($validator);
            $this->validateBkuSumEqualsRealisasi($validator);
        });
    }

    private function validateDetailBelongsToPenerimaan($validator): void
    {
        $detailId = $this->input('penerimaan_detail_id');
        $penerimaanId = $this->input('penerimaan_id');

        if ($detailId === null || $penerimaanId === null) {
            return;
        }

        $detail = PenerimaanDetail::find($detailId);
        if ($detail === null || (int) $detail->penerimaan_id !== (int) $penerimaanId) {
            $validator->errors()->add('penerimaan_detail_id', 'Detail tidak sesuai dengan Penerimaan yang dipilih.');
        }
    }

    private function validateActiveRekeningBanks($validator): void
    {
        $bkus = $this->input('bkus');

        if (! is_array($bkus)) {
            return;
        }

        foreach ($bkus as $index => $bku) {
            $bankId = $bku['rekening_bank_id'] ?? null;

            if ($bankId === null) {
                continue;
            }

            $bank = RekeningBank::find($bankId);

            if ($bank !== null && ! $bank->is_active) {
                $validator->errors()->add("bkus.{$index}.rekening_bank_id", 'Rekening bank tidak aktif dan tidak dapat digunakan.');
            }
        }
    }

    private function validateBkuSumEqualsRealisasi($validator): void
    {
        $bkus = $this->input('bkus');
        $realisasi = $this->input('realisasi');

        if (! is_array($bkus) || $bkus === [] || $realisasi === null) {
            return;
        }

        $total = 0.0;
        foreach ($bkus as $bku) {
            $total += (float) ($bku['nilai'] ?? 0);
        }

        if (abs($total - (float) $realisasi) > 0.0001) {
            $validator->errors()->add(
                'bkus',
                'Total nilai BKU harus sama dengan nilai transaksi penerimaan.'
            );
        }
    }
}
