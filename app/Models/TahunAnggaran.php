<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAnggaran extends Model
{
    protected $fillable = ['tahun', 'tanggal_mulai', 'tanggal_selesai', 'status', 'is_active'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    public function open(): void
    {
        $this->update(['status' => 'open']);
    }

    public function activate(): void
    {
        static::query()->where('is_active', true)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public static function currentActive(): ?static
    {
        return static::active()->first();
    }
}
