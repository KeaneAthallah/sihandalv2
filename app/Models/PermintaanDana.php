<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermintaanDana extends Model
{
    protected $fillable = [
        'nomor_permintaan', 'opd_id', 'sumber_dana_id', 'jumlah', 'sumber_dana',
        'keperluan', 'status', 'tanggal', 'tanggal_disetujui', 'catatan',
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

    public function persetujuans(): HasMany
    {
        return $this->hasMany(Persetujuan::class);
    }
}
