<?php

namespace App\Http\Requests\Api;

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\PermintaanDana;
use App\Models\SubKegiatan;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API variant of the Pengeluaran request, mirroring the web rules:
 *   - rekening must be tipe belanja
 *   - kegiatan -> sub kegiatan -> belanja hierarchy must be internally
 *     consistent and belong to the selected OPD
 *   - bila permintaan_dana_id diisi, seluruh field keuangan diambil
 *     dari permintaan dana (mode admin), hanya SP2D yang diinput
 */
class StorePengeluaranApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permintaan_dana_id' => ['nullable', 'exists:permintaan_danas,id'],
            'opd_id' => ['required_without:permintaan_dana_id', 'exists:opds,id'],
            'rekening_id' => ['nullable', Rule::exists('rekenings', 'id')->where(fn (Builder $q) => $q->where('tipe', 'belanja'))],
            'kegiatan_id' => ['nullable', 'exists:kegiatan,id'],
            'sub_kegiatan_id' => ['nullable', 'exists:sub_kegiatans,id'],
            'belanja_id' => ['nullable', 'exists:belanjas,id'],
            'sumber_dana_id' => ['required_without:permintaan_dana_id', 'exists:sumber_danas,id'],
            'sumber_dana' => ['nullable', 'string', 'max:255'],
            'jumlah' => ['required_without:permintaan_dana_id', 'numeric', 'min:0'],
            'keperluan' => ['nullable', 'string', 'max:255'],
            'no_sp2d' => ['nullable', 'string', 'max:100'],
            'tanggal_sp2d' => ['nullable', 'date'],
            'tanggal' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();

            // Mode dari permintaan dana: field keuangan
            // diambil dari permintaan dana, hanya SP2D
            // yang diinput. Wajib admin, PD harus
            // disetujui dan belum punya pengeluaran.
            $permintaanDanaId = $this->input('permintaan_dana_id');
            if ($permintaanDanaId) {
                if (! $user->isAdmin()) {
                    $validator->errors()->add('permintaan_dana_id', 'Hanya admin yang dapat membuat pengeluaran dari permintaan dana.');

                    return;
                }

                $pd = PermintaanDana::with('pengeluaran')->find($permintaanDanaId);

                if ($pd === null) {
                    return;
                }

                if ($pd->status !== 'disetujui') {
                    $validator->errors()->add('permintaan_dana_id', 'Hanya permintaan dana yang disetujui yang dapat dibuatkan pengeluaran.');
                }

                if ($pd->pengeluaran()->exists()) {
                    $validator->errors()->add('permintaan_dana_id', 'Permintaan dana ini sudah memiliki pengeluaran.');
                }

                return;
            }

            // Mode manual
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
