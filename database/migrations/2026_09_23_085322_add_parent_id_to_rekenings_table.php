<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds an optional self-referencing parent_id to the rekenings table.
     *
     * This enables the Rekening Kas hierarchy: a rekening with tipe 'kas' may
     * act as an induk (parent) account holding one or more detail (child) kas
     * accounts. Deletion of a parent that still has children is blocked at the
     * database level via RESTRICT; the application also guards this in the
     * controller.
     */
    public function up(): void
    {
        Schema::table('rekenings', function (Blueprint $table) {
            if (! Schema::hasColumn('rekenings', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('tipe')
                    ->constrained('rekenings')
                    ->restrictOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rekenings', function (Blueprint $table) {
            if (Schema::hasColumn('rekenings', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};
