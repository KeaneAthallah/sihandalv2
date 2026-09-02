<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separates "Penerimaan" into a master definition (penerimaans) and its
     * realization transactions (transaksi_penerimaans).
     *
     * The master keeps the definition fields (OPD, Rekening, Sumber Dana,
     * Tahun Anggaran, Target) plus the legacy denormalized text columns that
     * the rest of the app still reads for display. The realization-specific
     * fields (realisasi, persentase, tanggal, keterangan) move to the
     * transaction table, so a master's realization is always the SUM of its
     * transactions rather than a hand-entered value.
     *
     * Existing rows are preserved: each current penerimaans row becomes one
     * master plus one transaction carrying its realisasi/tanggal/keterangan and
     * (for imported rows) its source traceability. Province-wide imported rows
     * keep opd_id = NULL exactly as before.
     */
    public function up(): void
    {
        Schema::create('transaksi_penerimaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_id')->constrained('penerimaans')->cascadeOnDelete();
            $table->decimal('realisasi', 18, 2)->default(0);
            $table->date('tanggal')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('source_file')->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->string('source_identifier', 255)->nullable();
            $table->unique(['source_file', 'source_row']);
            $table->timestamps();
        });

        // Move realization + traceability off the master onto its transactions.
        if (Schema::hasColumn('penerimaans', 'realisasi')) {
            $this->backfillTransactions();
        }

        Schema::table('penerimaans', function (Blueprint $table) {
            // Drop indexes that reference columns this migration removes. On
            // SQLite a DROP COLUMN fails if the column is still indexed, and
            // the opd_id_tanggal index can only be dropped as a whole on
            // SQLite. On MySQL, DROP COLUMN prunes the index automatically, so
            // we leave the index intact there to avoid FK conflicts on opd_id.
            if (Schema::hasIndex('penerimaans', 'penerimaans_source_file_source_row_unique')) {
                $table->dropUnique(['source_file', 'source_row']);
            }
            if (DB::getDriverName() === 'sqlite' && Schema::hasIndex('penerimaans', 'penerimaans_opd_id_tanggal_index')) {
                $table->dropIndex(['opd_id', 'tanggal']);
            }
            if (Schema::hasColumn('penerimaans', 'source_row')) {
                $table->dropColumn(['source_row']);
            }
            if (Schema::hasColumn('penerimaans', 'realisasi')) {
                $table->dropColumn(['realisasi', 'persentase', 'tanggal', 'keterangan']);
            }
        });
    }

    public function down(): void
    {
        // Reintroduce the denormalized columns so the master can hold a
        // realization again if the change is ever rolled back. Existing
        // transactions are merged back onto their master (sum of realisasi).
        Schema::table('penerimaans', function (Blueprint $table) {
            $table->decimal('realisasi', 18, 2)->default(0)->after('target');
            $table->decimal('persentase', 5, 2)->default(0)->after('realisasi');
            $table->date('tanggal')->nullable()->after('persentase');
            $table->string('keterangan')->nullable()->after('tanggal');
            $table->unsignedInteger('source_row')->nullable()->after('source_identifier');
            $table->unique(['source_file', 'source_row']);
        });

        $this->backfillMasterFromTransactions();

        Schema::dropIfExists('transaksi_penerimaans');
    }

    private function backfillTransactions(): void
    {
        DB::table('penerimaans')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                DB::table('transaksi_penerimaans')->insert([
                    'penerimaan_id' => $row->id,
                    'realisasi' => (float) $row->realisasi,
                    'tanggal' => $row->tanggal,
                    'keterangan' => $row->keterangan,
                    'source_file' => $row->source_file,
                    'source_row' => $row->source_row,
                    'source_identifier' => $row->source_identifier,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    private function backfillMasterFromTransactions(): void
    {
        DB::table('transaksi_penerimaans')->orderBy('id')->chunkById(500, function ($rows) {
            $byMaster = [];
            foreach ($rows as $row) {
                $byMaster[$row->penerimaan_id] = ($byMaster[$row->penerimaan_id] ?? 0) + (float) $row->realisasi;
            }
            foreach ($byMaster as $masterId => $total) {
                $master = DB::table('penerimaans')->where('id', $masterId)->first();
                if ($master === null) {
                    continue;
                }
                DB::table('penerimaans')->where('id', $masterId)->update([
                    'realisasi' => $total,
                    'persentase' => $master->target > 0 ? round($total / $master->target * 100, 2) : 0,
                    'tanggal' => DB::table('transaksi_penerimaans')->where('penerimaan_id', $masterId)->max('tanggal'),
                ]);
            }
        });
    }
};
