<?php

namespace App\Http\Requests\Api;

/**
 * API update variant: identical rules to the store request. nomor_registrasi
 * is never accepted, so it cannot be modified through the API.
 */
class UpdateTransaksiPenerimaanApiRequest extends StoreTransaksiPenerimaanApiRequest {}
