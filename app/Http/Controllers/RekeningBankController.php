<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRekeningBankRequest;
use App\Http\Requests\UpdateRekeningBankRequest;
use App\Models\RekeningBank;

/**
 * Rekening Bank is a global master with no OPD scope and no dedicated pages:
 * it lives inside the Transaksi Penerimaan form, where a pop-up lets users add
 * or edit a bank on the spot.
 */
class RekeningBankController extends Controller
{
    /**
     * Add a new bank from the transaksi form's pop-up.
     */
    public function store(StoreRekeningBankRequest $request)
    {
        $bank = RekeningBank::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $bank->id,
                'label' => $bank->label,
                'message' => 'Rekening bank berhasil ditambahkan.',
            ]);
        }

        return back()->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    /**
     * Edit a bank from the transaksi form's pop-up.
     */
    public function update(UpdateRekeningBankRequest $request, RekeningBank $rekeningBank)
    {
        $rekeningBank->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $rekeningBank->id,
                'label' => $rekeningBank->label,
                'message' => 'Rekening bank berhasil diperbarui.',
            ]);
        }

        return back()->with('success', 'Rekening bank berhasil diperbarui.');
    }
}
