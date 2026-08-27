<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SumberDana extends Model
{
    use Auditable;

    protected $fillable = ['nama_sumber_dana'];
}
