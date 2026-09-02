<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            if (! Schema::hasColumn('pengeluarans', 'kegiatan_id')) {
                $table->foreignId('kegiatan_id')->nullable()->after('rekening_id')->constrained('kegiatan')->nullOnDelete();
            }
            if (! Schema::hasColumn('pengeluarans', 'sub_kegiatan_id')) {
                $table->foreignId('sub_kegiatan_id')->nullable()->after('kegiatan_id')->constrained('sub_kegiatans')->nullOnDelete();
            }
            if (! Schema::hasColumn('pengeluarans', 'belanja_id')) {
                $table->foreignId('belanja_id')->nullable()->after('sub_kegiatan_id')->constrained('belanjas')->nullOnDelete();
            }
            if (! Schema::hasColumn('pengeluarans', 'sumber_dana_id')) {
                $table->foreignId('sumber_dana_id')->nullable()->after('belanja_id')->constrained('sumber_danas')->nullOnDelete();
            }
            if (! Schema::hasColumn('pengeluarans', 'tahun_anggaran_id')) {
                $table->foreignId('tahun_anggaran_id')->nullable()->after('sumber_dana_id')->constrained('tahun_anggarans')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->dropForeign(['tahun_anggaran_id']);
            $table->dropForeign(['sumber_dana_id']);
            $table->dropForeign(['belanja_id']);
            $table->dropForeign(['sub_kegiatan_id']);
            $table->dropForeign(['kegiatan_id']);
            $table->dropColumn(['kegiatan_id', 'sub_kegiatan_id', 'belanja_id', 'sumber_dana_id', 'tahun_anggaran_id']);
        });
    }
};
