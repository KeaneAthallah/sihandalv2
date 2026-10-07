<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengeluaran extends Model
{
    use Auditable;

    protected $fillable = [
        'opd_id', 'rekening_id', 'kegiatan_id', 'sub_kegiatan_id', 'belanja_id',
        'sumber_dana_id', 'tahun_anggaran_id', 'permintaan_dana_id',
        'sumber_dana', 'jumlah', 'keperluan',
        'no_sp2d', 'tanggal_sp2d',
        'tanggal',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal' => 'date',
        'tanggal_sp2d' => 'date',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class);
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function belanja(): BelongsTo
    {
        return $this->belongsTo(Belanja::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    /**
     * The approved fund request this expenditure was derived from,
     * if any. Mirrors the request so OPD, sumber dana, kegiatan,
     * sub kegiatan, belanja, rekening, jumlah and keperluan come
     * from the request rather than from user input.
     */
    public function permintaanDana(): BelongsTo
    {
        return $this->belongsTo(PermintaanDana::class);
    }
}
