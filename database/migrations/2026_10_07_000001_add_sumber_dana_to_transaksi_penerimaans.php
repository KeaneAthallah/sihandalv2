<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kas masuk dikaitkan ke sumber dana pada level transaksi,
     * sehingga saldo kas dapat dihitung per sumber dana dan
     * transfer dana bisa memindahkan kas antar sumber dana.
     */
    public function up(): void
    {
        Schema::table('transaksi_penerimaans', function (Blueprint $table) {
            $table->foreignId('sumber_dana_id')
                ->nullable()
                ->after('penerimaan_id')
                ->constrained('sumber_danas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penerimaans', function (Blueprint $table) {
            $table->dropForeign(['sumber_dana_id']);
            $table->dropColumn('sumber_dana_id');
        });
    }
};
