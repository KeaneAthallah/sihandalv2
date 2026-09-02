<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the persisted `saldo` column. Cash balances are now derived from
     * financial transactions (SUM(transaksi_penerimaans.realisasi) minus
     * SUM(pengeluarans.realisasi)) instead of being stored on the master.
     */
    public function up(): void
    {
        Schema::table('rekenings', function (Blueprint $table) {
            $table->dropColumn('saldo');
        });
    }

    /**
     * Restore the persisted `saldo` column (defaults to 0, since balances are
     * no longer stored and cannot be reconstructed from the master alone).
     */
    public function down(): void
    {
        Schema::table('rekenings', function (Blueprint $table) {
            $table->decimal('saldo', 18, 2)->default(0)->after('tipe');
        });
    }
};
