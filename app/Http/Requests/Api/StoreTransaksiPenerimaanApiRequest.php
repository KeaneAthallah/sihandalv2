<?php

namespace App\Http\Requests\Api;

use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\RekeningBank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API variant of the transaksi penerimaan request. Mirrors the web
 * TransaksiPenerimaanRequest rules including nested BKU validation:
 *   - OPD ownership of the selected Penerimaan master
 *   - each BKU row's rekening utama must be tipe pendapatan and a
 *     sub rekening must be its detail
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
            'sumber_dana_id' => ['required', 'exists:sumber_danas,id'],
            'realisasi' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'bkus' => ['nullable', 'array'],
            'bkus.*.id' => ['sometimes', 'nullable', 'integer', 'exists:transaksi_penerimaan_bkus,id'],
            'bkus.*.opd_id' => ['nullable', 'integer', 'exists:opds,id'],
            'bkus.*.nomor_bku' => ['required_with:bkus', 'string', 'max:100'],
            'bkus.*.tanggal_bku' => ['required_with:bkus', 'date'],
            'bkus.*.nilai' => ['required_with:bkus', 'numeric', 'min:0'],
            'bkus.*.rekening_id' => ['nullable', 'integer', Rule::exists('rekenings', 'id')->where(fn ($q) => $q->where('tipe', 'pendapatan'))],
            'bkus.*.sub_rekening_id' => ['nullable', 'integer', 'exists:rekenings,id'],
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

            $this->validateBkuRekenings($validator);
            $this->validateActiveRekeningBanks($validator);
            $this->validateBkuSumEqualsRealisasi($validator);
        });
    }

    /**
     * A BKU row's rekening utama must be a pendapatan rekening, and a
     * sub rekening must be a detail of that rekening utama.
     */
    private function validateBkuRekenings($validator): void
    {
        $bkus = $this->input('bkus');

        if (! is_array($bkus)) {
            return;
        }

        foreach ($bkus as $index => $bku) {
            $rekeningId = $bku['rekening_id'] ?? null;
            $subRekeningId = $bku['sub_rekening_id'] ?? null;

            if ($rekeningId !== null) {
                $rekening = Rekening::find($rekeningId);

                if ($rekening !== null && $rekening->tipe !== 'pendapatan') {
                    $validator->errors()->add("bkus.{$index}.rekening_id", 'Rekening utama pada BKU harus bertipe pendapatan.');
                }
            }

            if ($subRekeningId === null) {
                continue;
            }

            $sub = Rekening::find($subRekeningId);
            if ($sub === null) {
                continue;
            }

            if ($rekeningId === null) {
                $validator->errors()->add("bkus.{$index}.sub_rekening_id", 'Pilih rekening utama terlebih dahulu sebelum memilih sub rekening.');
            } elseif ((int) $sub->parent_id !== (int) $rekeningId) {
                $validator->errors()->add("bkus.{$index}.sub_rekening_id", 'Sub rekening harus merupakan detail dari rekening utama pada BKU ini.');
            }
        }
    }

    /**
     * A booked Rekening Bank on any BKU row must be active, so revenue is never
     * parked into a deactivated account.
     */
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

    /**
     * The sum of all BKU details must exactly match the transaction realisasi,
     * but only when at least one BKU row exists. A transaction with zero BKU is
     * valid regardless of realisasi (BKU may be completed later).
     */
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
