<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermintaanDana extends Model
{
    use Auditable;

    protected $fillable = [
        'nomor_permintaan', 'opd_id', 'sumber_dana_id', 'kegiatan_id',
        'sub_kegiatan_id', 'belanja_id', 'rekening_id', 'tahun_anggaran_id',
        'jumlah', 'sumber_dana', 'keperluan', 'status',
        'tanggal', 'tanggal_disetujui', 'catatan',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal' => 'date',
        'tanggal_disetujui' => 'date',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class, 'sumber_dana_id');
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

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class);
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function persetujuans(): HasMany
    {
        return $this->hasMany(Persetujuan::class);
    }
}
