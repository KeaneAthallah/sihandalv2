<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaans', function (Blueprint $table) {
            if (! Schema::hasColumn('penerimaans', 'sumber_dana_id')) {
                $table->foreignId('sumber_dana_id')->nullable()->after('rekening_id')->constrained('sumber_danas')->nullOnDelete();
            }
            if (! Schema::hasColumn('penerimaans', 'tahun_anggaran_id')) {
                $table->foreignId('tahun_anggaran_id')->nullable()->after('sumber_dana_id')->constrained('tahun_anggarans')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('penerimaans', function (Blueprint $table) {
            $table->dropForeign(['tahun_anggaran_id']);
            $table->dropForeign(['sumber_dana_id']);
            $table->dropColumn(['sumber_dana_id', 'tahun_anggaran_id']);
        });
    }
};
