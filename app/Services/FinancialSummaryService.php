<?php

namespace App\Services;

use App\Models\Belanja;
use App\Models\Opd;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\TahunAnggaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Shared SQL-aggregate financial summaries.
 *
 * Used by the dashboard, AI summary and report API endpoints (and reusable by
 * the web dashboard). All totals are computed with aggregate SQL queries -
 * never by loading full tables into PHP.
 */
class FinancialSummaryService
{
    /**
     * Determine the OPD id scope for a user, respecting an explicit opd_id
     * filter that only admins may apply.
     *
     * @return int|null OPD id to filter by, or null for "no filter" (admin).
     */
    public static function opdScope(?User $user, ?int $requestedOpdId = null): ?int
    {
        if ($user !== null && ! $user->isAdmin()) {
            return (int) $user->opd_id;
        }

        return $requestedOpdId;
    }

    /**
     * Apply the OPD scope to a query with the given opd_id column.
     */
    public static function applyOpd(Builder $query, ?User $user, ?int $requestedOpdId = null, string $column = 'opd_id'): Builder
    {
        $opdId = static::opdScope($user, $requestedOpdId);

        if ($opdId !== null) {
            $query->where($column, $opdId);
        }

        return $query;
    }

    /**
     * Budget aggregate over the Belanja leaf: pagu, realisasi, commit, available.
     *
     * @return array{pagu: float, realisasi: float, commit: float, available: float}
     */
    public static function budgetTotals(?User $user, ?int $requestedOpdId = null, ?int $tahunAnggaranId = null): array
    {
        $query = Belanja::query()
            ->selectRaw('COALESCE(SUM(pagu), 0) as pagu')
            ->selectRaw('COALESCE(SUM(realisasi), 0) as realisasi')
            ->selectRaw('COALESCE(SUM(dana_di_commit), 0) as dana_di_commit');

        static::applyOpd($query, $user, $requestedOpdId);

        if ($tahunAnggaranId !== null) {
            $query->where('tahun_anggaran_id', $tahunAnggaranId);
        }

        $row = $query->first();

        $pagu = (float) ($row->pagu ?? 0);
        $realisasi = (float) ($row->realisasi ?? 0);
        $commit = (float) ($row->dana_di_commit ?? 0);

        return [
            'pagu' => $pagu,
            'realisasi' => $realisasi,
            'commit' => $commit,
            'available' => round($pagu - $realisasi - $commit, 2),
        ];
    }

    /**
     * Realized revenue total from transaction aggregation.
     */
    public static function revenueTotal(?User $user, ?int $requestedOpdId = null, ?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = DB::table('transaksi_penerimaans')
            ->join('penerimaans', 'penerimaans.id', '=', 'transaksi_penerimaans.penerimaan_id');

        $opdId = static::opdScope($user, $requestedOpdId);
        if ($opdId !== null) {
            $query->where('penerimaans.opd_id', $opdId);
        }

        if ($dateFrom !== null) {
            $query->whereDate('transaksi_penerimaans.tanggal', '>=', $dateFrom);
        }

        if ($dateTo !== null) {
            $query->whereDate('transaksi_penerimaans.tanggal', '<=', $dateTo);
        }

        return (float) $query->sum('transaksi_penerimaans.realisasi');
    }

    /**
     * Realized expenditure total.
     */
    public static function expenditureTotal(?User $user, ?int $requestedOpdId = null, ?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = Pengeluaran::query();

        static::applyOpd($query, $user, $requestedOpdId);

        if ($dateFrom !== null) {
            $query->whereDate('tanggal', '>=', $dateFrom);
        }

        if ($dateTo !== null) {
            $query->whereDate('tanggal', '<=', $dateTo);
        }

        return (float) $query->sum('jumlah');
    }

    /**
     * Cash balance: penerimaan (kas rekening) minus pengeluaran (kas rekening).
     *
     * @return array{kas_penerimaan: float, kas_pengeluaran: float, saldo_kas: float}
     */
    public static function cashTotals(?User $user, ?int $requestedOpdId = null): array
    {
        $opdId = static::opdScope($user, $requestedOpdId);

        $kasPenerimaan = (float) DB::table('transaksi_penerimaans as t')
            ->join('penerimaans as p', 'p.id', '=', 't.penerimaan_id')
            ->join('rekenings as r', 'r.id', '=', 'p.rekening_id')
            ->when($opdId !== null, fn ($q) => $q->where('p.opd_id', $opdId))
            ->where('r.tipe', 'kas')
            ->sum('t.realisasi');

        $kasPengeluaran = (float) DB::table('pengeluarans')
            ->join('rekenings as r', 'r.id', '=', 'pengeluarans.rekening_id')
            ->when($opdId !== null, fn ($q) => $q->where('pengeluarans.opd_id', $opdId))
            ->where('r.tipe', 'kas')
            ->sum('pengeluarans.jumlah');

        return [
            'kas_penerimaan' => $kasPenerimaan,
            'kas_pengeluaran' => $kasPengeluaran,
            'saldo_kas' => round($kasPenerimaan - $kasPengeluaran, 2),
        ];
    }

    /**
     * Permintaan dana status counts (and sums) within the user's OPD scope.
     *
     * @return array{draft: int, menunggu: int, disetujui: int, ditolak: int}
     */
    public static function permintaanDanaCounts(?User $user, ?int $requestedOpdId = null): array
    {
        $query = PermintaanDana::query()
            ->selectRaw('status, COUNT(*) as total, COALESCE(SUM(jumlah), 0) as nilai')
            ->groupBy('status');

        static::applyOpd($query, $user, $requestedOpdId);

        $rows = $query->get();

        $counts = ['draft' => 0, 'menunggu' => 0, 'disetujui' => 0, 'ditolak' => 0];

        foreach ($rows as $row) {
            if (array_key_exists($row->status, $counts)) {
                $counts[$row->status] = (int) $row->total;
            }
        }

        return $counts;
    }

    /**
     * Per-program budget aggregate (pagu/realisasi derived from Belanja leaves
     * through the sub kegiatan -> kegiatan -> program chain).
     *
     * @return array<int, array{id: int, kode_program: string|null, nama_program: string|null, pagu: float, realisasi: float, percentage: float}>
     */
    public static function programTotals(?User $user, ?int $requestedOpdId = null, ?int $tahunAnggaranId = null): array
    {
        $opdId = static::opdScope($user, $requestedOpdId);

        $rows = DB::table('belanjas')
            ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'belanjas.sub_kegiatan_id')
            ->join('kegiatan', 'kegiatan.id', '=', 'sub_kegiatans.kegiatan_id')
            ->join('programs', 'programs.id', '=', 'kegiatan.program_id')
            ->when($opdId !== null, fn ($q) => $q->where('belanjas.opd_id', $opdId))
            ->when($tahunAnggaranId !== null, fn ($q) => $q->where('belanjas.tahun_anggaran_id', $tahunAnggaranId))
            ->selectRaw('programs.id, programs.kode_program, programs.nama_program')
            ->selectRaw('COALESCE(SUM(belanjas.pagu), 0) as pagu')
            ->selectRaw('COALESCE(SUM(belanjas.realisasi), 0) as realisasi')
            ->groupBy('programs.id', 'programs.kode_program', 'programs.nama_program')
            ->orderByDesc('pagu')
            ->get();

        return $rows->map(fn ($row) => [
            'id' => (int) $row->id,
            'kode_program' => $row->kode_program,
            'nama_program' => $row->nama_program,
            'pagu' => (float) $row->pagu,
            'realisasi' => (float) $row->realisasi,
            'percentage' => (float) $row->pagu > 0 ? round((float) $row->realisasi / (float) $row->pagu * 100, 2) : 0.0,
        ])->all();
    }

    /**
     * The active fiscal year, if any.
     */
    public static function activeTahunAnggaran(): ?TahunAnggaran
    {
        return TahunAnggaran::query()->active()->first();
    }

    /**
     * Percentage helper.
     */
    public static function percentage(float $part, float $whole): float
    {
        return $whole > 0 ? round($part / $whole * 100, 2) : 0.0;
    }
}
