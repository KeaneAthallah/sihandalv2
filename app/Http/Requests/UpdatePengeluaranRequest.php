<?php

namespace App\Http\Requests;

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePengeluaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opds,id'],
            'rekening_id' => ['nullable', Rule::exists('rekenings', 'id')->where(fn (Builder $q) => $q->where('tipe', 'belanja'))],
            'kegiatan_id' => ['nullable', 'exists:kegiatan,id'],
            'sub_kegiatan_id' => ['nullable', 'exists:sub_kegiatans,id'],
            'belanja_id' => ['nullable', 'exists:belanjas,id'],
            'sumber_dana_id' => ['required', 'exists:sumber_danas,id'],
            'kode_kegiatan' => ['nullable', 'string', 'max:50'],
            'nama_kegiatan' => ['nullable', 'string', 'max:255'],
            'sumber_dana' => ['nullable', 'string', 'max:255'],
            'anggaran' => ['required', 'numeric', 'min:0'],
            'realisasi' => ['nullable', 'numeric', 'min:0'],
            'tanggal' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $opdId = $this->input('opd_id');

            if (! $user->isAdmin() && (int) $opdId !== (int) $user->opd_id) {
                $validator->errors()->add('opd_id', 'Anda hanya dapat mengelola pengeluaran untuk OPD Anda sendiri.');
            }

            $kegiatanId = $this->input('kegiatan_id');
            if ($kegiatanId) {
                $kegiatan = Kegiatan::find($kegiatanId);
                if ($kegiatan && (int) $kegiatan->opd_id !== (int) $opdId) {
                    $validator->errors()->add('kegiatan_id', 'Kegiatan tidak sesuai dengan OPD yang dipilih.');
                }
            }

            $subKegiatanId = $this->input('sub_kegiatan_id');
            if ($subKegiatanId) {
                $subKegiatan = SubKegiatan::with('kegiatan')->find($subKegiatanId);
                if ($subKegiatan) {
                    if ($kegiatanId && (int) $subKegiatan->kegiatan_id !== (int) $kegiatanId) {
                        $validator->errors()->add('sub_kegiatan_id', 'Sub kegiatan tidak sesuai dengan kegiatan yang dipilih.');
                    }

                    if ((int) $subKegiatan->kegiatan?->opd_id !== (int) $opdId) {
                        $validator->errors()->add('sub_kegiatan_id', 'Sub kegiatan tidak sesuai dengan OPD yang dipilih.');
                    }
                }
            }

            $belanjaId = $this->input('belanja_id');
            if ($belanjaId) {
                $belanja = Belanja::find($belanjaId);
                if ($belanja) {
                    if ($subKegiatanId && (int) $belanja->sub_kegiatan_id !== (int) $subKegiatanId) {
                        $validator->errors()->add('belanja_id', 'Belanja tidak sesuai dengan sub kegiatan yang dipilih.');
                    }

                    if ((int) $belanja->opd_id !== (int) $opdId) {
                        $validator->errors()->add('belanja_id', 'Belanja tidak sesuai dengan OPD yang dipilih.');
                    }
                }
            }

            if ($validator->errors()->has('rekening_id')) {
                $validator->errors()->forget('rekening_id');
                $validator->errors()->add('rekening_id', 'Rekening pengeluaran harus bertipe belanja.');
            }
        });
    }
}
