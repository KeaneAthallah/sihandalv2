<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaan_danas', function (Blueprint $table) {
            if (! Schema::hasColumn('permintaan_danas', 'kegiatan_id')) {
                $table->foreignId('kegiatan_id')->nullable()->after('sumber_dana_id')->constrained('kegiatan')->nullOnDelete();
            }
            if (! Schema::hasColumn('permintaan_danas', 'sub_kegiatan_id')) {
                $table->foreignId('sub_kegiatan_id')->nullable()->after('kegiatan_id')->constrained('sub_kegiatans')->nullOnDelete();
            }
            if (! Schema::hasColumn('permintaan_danas', 'belanja_id')) {
                $table->foreignId('belanja_id')->nullable()->after('sub_kegiatan_id')->constrained('belanjas')->nullOnDelete();
            }
            if (! Schema::hasColumn('permintaan_danas', 'rekening_id')) {
                $table->foreignId('rekening_id')->nullable()->after('belanja_id')->constrained('rekenings')->nullOnDelete();
            }
            if (! Schema::hasColumn('permintaan_danas', 'tahun_anggaran_id')) {
                $table->foreignId('tahun_anggaran_id')->nullable()->after('rekening_id')->constrained('tahun_anggarans')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_danas', function (Blueprint $table) {
            $table->dropForeign(['tahun_anggaran_id']);
            $table->dropForeign(['rekening_id']);
            $table->dropForeign(['belanja_id']);
            $table->dropForeign(['sub_kegiatan_id']);
            $table->dropForeign(['kegiatan_id']);
            $table->dropColumn(['kegiatan_id', 'sub_kegiatan_id', 'belanja_id', 'rekening_id', 'tahun_anggaran_id']);
        });
    }
};
