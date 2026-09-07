<?php

namespace App\Http\Requests;

class UpdateTransaksiPenerimaanRequest extends TransaksiPenerimaanRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
