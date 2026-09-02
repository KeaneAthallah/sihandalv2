<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            if (! Schema::hasColumn('kegiatan', 'rekening_id')) {
                $table->foreignId('rekening_id')->nullable()->after('sumber_dana_id')->constrained('rekenings')->nullOnDelete();
            }
            if (! Schema::hasColumn('kegiatan', 'tahun_anggaran_id')) {
                $table->foreignId('tahun_anggaran_id')->nullable()->after('rekening_id')->constrained('tahun_anggarans')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropForeign(['tahun_anggaran_id']);
            $table->dropForeign(['rekening_id']);
            $table->dropColumn(['rekening_id', 'tahun_anggaran_id']);
        });
    }
};
