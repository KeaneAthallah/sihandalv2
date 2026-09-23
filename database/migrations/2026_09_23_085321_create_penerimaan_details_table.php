<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Introduces detail records for a Penerimaan master.
     *
     * Each PenerimaanDetail selects a Rekening Bank (pendapatan rekening) and
     * a Sumber Dana at the detail level, while the master keeps its existing
     * (legacy) rekening_id / sumber_dana_id columns for backward compatibility.
     *
     * Realization is NOT stored here: it stays on transaksi_penerimaans and a
     * master's realization remains the SUM of its transactions. Transactions
     * may optionally reference a PenerimaanDetail through the nullable
     * transaksi_penerimaans.penerimaan_detail_id link.
     *
     * Existing masters are preserved: any master that already carries a
     * rekening_id and/or sumber_dana_id gets one backfilled detail row.
     */
    public function up(): void
    {
        Schema::create('penerimaan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_id')->constrained('penerimaans')->cascadeOnDelete();
            $table->foreignId('rekening_id')->nullable()->constrained('rekenings')->nullOnDelete();
            $table->foreignId('sumber_dana_id')->nullable()->constrained('sumber_danas')->nullOnDelete();
            $table->timestamps();

            $table->index('penerimaan_id');
            $table->index('rekening_id');
            $table->index('sumber_dana_id');
        });

        $this->backfillDetailsFromMaster();

        Schema::table('transaksi_penerimaans', function (Blueprint $table) {
            if (! Schema::hasColumn('transaksi_penerimaans', 'penerimaan_detail_id')) {
                $table->foreignId('penerimaan_detail_id')
                    ->nullable()
                    ->after('penerimaan_id')
                    ->constrained('penerimaan_details')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penerimaans', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi_penerimaans', 'penerimaan_detail_id')) {
                $table->dropForeign(['penerimaan_detail_id']);
                $table->dropColumn('penerimaan_detail_id');
            }
        });

        Schema::dropIfExists('penerimaan_details');
    }

    private function backfillDetailsFromMaster(): void
    {
        DB::table('penerimaans')
            ->orderBy('id')
            ->where(function ($q) {
                $q->whereNotNull('rekening_id')->orWhereNotNull('sumber_dana_id');
            })
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('penerimaan_details')->insert([
                        'penerimaan_id' => $row->id,
                        'rekening_id' => $row->rekening_id,
                        'sumber_dana_id' => $row->sumber_dana_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }
};
