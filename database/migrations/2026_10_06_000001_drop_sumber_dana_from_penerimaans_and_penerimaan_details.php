<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penerimaan masters are keyed by rekening (utama + sub), not by
     * sumber dana. Drop the sumber dana columns and the PenerimaanDetail
     * rows, and add the sub rekening link.
     */
    public function up(): void
    {
        Schema::table('transaksi_penerimaans', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi_penerimaans', 'penerimaan_detail_id')) {
                $table->dropForeign(['penerimaan_detail_id']);
                $table->dropColumn('penerimaan_detail_id');
            }
        });

        Schema::dropIfExists('penerimaan_details');

        Schema::table('penerimaans', function (Blueprint $table) {
            if (Schema::hasColumn('penerimaans', 'sumber_dana_id')) {
                $table->dropForeign(['sumber_dana_id']);
                $table->dropColumn('sumber_dana_id');
            }

            $table->dropColumn(['kode_sumber_dana', 'nama_sumber_dana']);

            $table->foreignId('sub_rekening_id')
                ->nullable()
                ->after('rekening_id')
                ->constrained('rekenings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penerimaans', function (Blueprint $table) {
            $table->dropForeign(['sub_rekening_id']);
            $table->dropColumn('sub_rekening_id');

            $table->string('kode_sumber_dana', 50)->nullable();
            $table->string('nama_sumber_dana')->nullable();
            $table->foreignId('sumber_dana_id')
                ->nullable()
                ->after('rekening_id')
                ->constrained('sumber_danas')
                ->nullOnDelete();
        });

        Schema::create('penerimaan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_id')->constrained('penerimaans')->cascadeOnDelete();
            $table->foreignId('rekening_id')->nullable()->constrained('rekenings')->nullOnDelete();
            $table->foreignId('sumber_dana_id')->nullable()->constrained('sumber_danas')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('transaksi_penerimaans', function (Blueprint $table) {
            $table->foreignId('penerimaan_detail_id')
                ->nullable()
                ->after('penerimaan_id')
                ->constrained('penerimaan_details')
                ->nullOnDelete();
        });
    }
};
