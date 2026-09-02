<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            if (! Schema::hasColumn('opds', 'nmskpd')) {
                $table->string('nmskpd', 255)->nullable()->after('nama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            $table->dropColumn('nmskpd');
        });
    }
};
