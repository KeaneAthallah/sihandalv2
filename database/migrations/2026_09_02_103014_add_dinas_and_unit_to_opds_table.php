<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            if (! Schema::hasColumn('opds', 'dinas_id')) {
                $table->foreignId('dinas_id')->nullable()->after('nmskpd')->constrained('dinas')->nullOnDelete();
            }
            if (! Schema::hasColumn('opds', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->after('dinas_id')->constrained('units')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['dinas_id']);
            $table->dropColumn(['dinas_id', 'unit_id']);
        });
    }
};
