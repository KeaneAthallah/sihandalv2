<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'kode_program', 'nama_program', 'opd_id', 'tahun_anggaran_id',
        'source_file', 'source_identifier',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function kegiatans(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }

    public function paguKegiatan()
    {
        return $this->kegiatans()->sum('pagu');
    }

    public function realisasiKegiatan()
    {
        return $this->kegiatans()->sum('realisasi');
    }
}
