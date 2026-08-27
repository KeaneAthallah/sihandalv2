<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permintaan_danas', function (Blueprint $table) {
            $table->foreignId('sumber_dana_id')->nullable()->after('opd_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permintaan_danas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sumber_dana_id');
        });
    }
};
