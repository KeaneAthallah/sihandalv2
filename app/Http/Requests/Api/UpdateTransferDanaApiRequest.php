<?php

namespace App\Http\Requests\Api;

use App\Services\KasService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * API variant of the TransferDana update request. Status transitions are
 * whitelisted; the controller further guards that selesai is final.
 * A transfer moves funds between two different sumber dana.
 */
class UpdateTransferDanaApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'sumber_dana_pengirim_id' => ['required', 'integer', 'exists:sumber_danas,id'],
            'sumber_dana_penerima_id' => ['required', 'integer', 'exists:sumber_danas,id', 'different:sumber_dana_pengirim_id'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,diproses,selesai,gagal'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('sumber_dana_pengirim_id') !== null
                && $this->input('sumber_dana_pengirim_id') === $this->input('sumber_dana_penerima_id')) {
                $validator->errors()->add('sumber_dana_penerima_id', 'Sumber dana pengirim dan penerima tidak boleh sama.');
            }

            // Pra-cek kas pengirim saat transfer akan
            // diselesaikan. Validasi otoritatif (dengan
            // kunci baris) dilakukan pada layanan.
            if ($this->input('status') === 'selesai') {
                $opdId = $this->input('opd_id');
                $pengirimId = $this->input('sumber_dana_pengirim_id');
                $jumlah = $this->input('jumlah');

                if ($opdId !== null && $pengirimId !== null && $jumlah !== null) {
                    $kasTersedia = app(KasService::class)->saldoTersedia((int) $opdId, (int) $pengirimId);

                    if ((float) $jumlah > $kasTersedia) {
                        $validator->errors()->add('jumlah', 'Kas pada sumber dana pengirim tidak mencukupi untuk transfer ini.');
                    }
                }
            }
        });
    }
}
