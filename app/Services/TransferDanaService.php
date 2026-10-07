<?php

namespace App\Services;

use App\Models\TransferDana;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransferDanaService
{
    /**
     * Complete an inter-fund transfer: check that the sending fund
     * source actually has enough cash, then mark it finished.
     *
     * The movement itself is derived — a transfer with status
     * 'selesai' is counted as cash out of the sender and cash in
     * of the receiver when each source's balance is computed — so
     * no separate balance table has to be updated here.
     */
    public function selesaikan(TransferDana $transferDana): TransferDana
    {
        return DB::transaction(function () use ($transferDana) {
            $locked = TransferDana::query()
                ->whereKey($transferDana->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'selesai') {
                throw new RuntimeException('Transfer dana ini sudah selesai.');
            }

            if ($locked->status === 'gagal') {
                throw new RuntimeException('Transfer dana yang gagal tidak dapat diselesaikan.');
            }

            $kasTersedia = app(KasService::class)->saldoTersedia(
                (int) $locked->opd_id,
                (int) $locked->sumber_dana_pengirim_id,
            );

            if ((float) $locked->jumlah > $kasTersedia) {
                throw new RuntimeException('Kas pada sumber dana pengirim tidak mencukupi untuk transfer ini.');
            }

            $locked->update([
                'status' => 'selesai',
                'tanggal_selesai' => now(),
            ]);

            return $locked->fresh();
        });
    }
}
