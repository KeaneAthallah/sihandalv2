<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drops the read-only laporan_snapshots table that previously stored
     * immutable BPKAD PDF report snapshots. This table is no longer used now
     * that the PDF import feature has been removed. It holds no transactional
     * finance data and shares no foreign keys with any other table.
     */
    public function up(): void
    {
        Schema::dropIfExists('laporan_snapshots');
    }

    /**
     * Recreates the table as an empty shell if the removal is ever rolled back.
     */
    public function down(): void
    {
        Schema::create('laporan_snapshots', function ($table) {
            $table->id();
            $table->string('jenis');
            $table->timestamps();
        });
    }
};
