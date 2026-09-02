<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            if (! Schema::hasColumn('programs', 'opd_id')) {
                $table->foreignId('opd_id')->nullable()->after('id')->constrained('opds')->nullOnDelete();
            }
            if (! Schema::hasColumn('programs', 'tahun_anggaran_id')) {
                $table->foreignId('tahun_anggaran_id')->nullable()->after('opd_id')->constrained('tahun_anggarans')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['tahun_anggaran_id']);
            $table->dropForeign(['opd_id']);
            $table->dropColumn(['opd_id', 'tahun_anggaran_id']);
        });
    }
};
