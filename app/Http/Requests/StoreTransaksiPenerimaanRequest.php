<?php

namespace App\Http\Requests;

class StoreTransaksiPenerimaanRequest extends TransaksiPenerimaanRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
