<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The free-text `catatan` is no longer part of the Permintaan Dana
     * input. Approval notes live on `persetujuans.catatan` and are
     * unaffected.
     */
    public function up(): void
    {
        Schema::table('permintaan_danas', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_danas', function (Blueprint $table) {
            $table->text('catatan')->nullable()->after('keperluan');
        });
    }
};
