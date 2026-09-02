<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Support the Sihandal data import system.
     *
     * 1. Makes penerimaans.opd_id nullable so province-wide revenue (the
     *    PENERIMAAN workbook) can be stored without an invented per-OPD owner.
     * 2. Adds source traceability columns (source_file / source_row /
     *    source_identifier) so every imported record can be traced back to
     *    `file row N` (leaf tables) or `file` + a stable key (master tables).
     * 3. Adds a deterministic (source_file, source_row) unique key on the
     *    leaf import tables (belanjas, penerimaans) so the import is safe to
     *    re-run without creating duplicates.
     */
    public function up(): void
    {
        Schema::table('penerimaans', function (Blueprint $table) {
            if (Schema::hasColumn('penerimaans', 'opd_id')) {
                $table->dropForeign(['opd_id']);
                $table->unsignedBigInteger('opd_id')->nullable()->change();
                $table->foreign('opd_id')->references('id')->on('opds')->cascadeOnDelete();
            }
        });

        // Leaf tables: full row-level traceability + unique import key.
        foreach (['belanjas', 'penerimaans'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('source_file')->nullable()->after('updated_at');
                $table->unsignedInteger('source_row')->nullable()->after('source_file');
                $table->string('source_identifier', 255)->nullable()->after('source_row');

                $table->unique(['source_file', 'source_row']);
            });
        }

        // Master / mid tables: lightweight provenance (file + stable key).
        foreach (['opds', 'programs', 'rekenings', 'sumber_danas', 'kegiatan', 'sub_kegiatans'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('source_file')->nullable()->after('updated_at');
                $table->string('source_identifier', 255)->nullable()->after('source_file');
            });
        }
    }

    public function down(): void
    {
        foreach (['belanjas', 'penerimaans'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['source_file', 'source_row']);
                $table->dropColumn(['source_identifier', 'source_row', 'source_file']);
            });
        }

        foreach (['opds', 'programs', 'rekenings', 'sumber_danas', 'kegiatan', 'sub_kegiatans'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['source_identifier', 'source_file']);
            });
        }

        Schema::table('penerimaans', function (Blueprint $table) {
            if (Schema::hasColumn('penerimaans', 'opd_id')) {
                $table->dropForeign(['opd_id']);
                $table->unsignedBigInteger('opd_id')->nullable(false)->change();
                $table->foreign('opd_id')->references('id')->on('opds')->cascadeOnDelete();
            }
        });
    }
};
