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
        'sumber_dana_id', 'tahun_anggaran_id',
        'kode_kegiatan', 'nama_kegiatan',
        'sumber_dana', 'anggaran', 'realisasi', 'persentase',
        'tanggal', 'keterangan',
    ];

    protected $casts = [
        'anggaran' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'persentase' => 'decimal:2',
        'tanggal' => 'date',
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
}
