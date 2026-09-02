<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';

    protected $fillable = [
        'program_id', 'opd_id', 'sumber_dana_id', 'rekening_id', 'tahun_anggaran_id',
        'kode_kegiatan', 'nama_kegiatan',
        'kode_sub_kegiatan', 'nama_sub_kegiatan',
        'kode_rekening', 'nama_rekening',
        'pagu', 'realisasi', 'persentase',
        'source_file', 'source_identifier',
    ];

    protected $casts = [
        'pagu' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'persentase' => 'decimal:2',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class);
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function subKegiatans(): HasMany
    {
        return $this->hasMany(SubKegiatan::class);
    }

    public function paguSubKegiatan()
    {
        return $this->subKegiatans()->sum('pagu');
    }

    public function realisasiSubKegiatan()
    {
        return $this->subKegiatans()->sum('realisasi');
    }

    public function belanjas(): HasManyThrough
    {
        return $this->hasManyThrough(Belanja::class, SubKegiatan::class, 'kegiatan_id', 'sub_kegiatan_id');
    }
}
