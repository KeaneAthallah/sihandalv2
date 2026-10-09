<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\PermintaanDana;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Models\TransferDana;
use App\Models\User;
use App\Services\FinancialSummaryService;
use App\Services\KasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private readonly KasService $kasService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();
        $tahunAnggaran = FinancialSummaryService::activeTahunAnggaran();
        $tahunAnggaranId = $tahunAnggaran?->id;

        $budget = FinancialSummaryService::budgetTotals($user, null, $tahunAnggaranId);
        $kas = FinancialSummaryService::cashTotals($user);
        $permintaanCounts = FinancialSummaryService::permintaanDanaCounts($user);
        $programTotals = array_slice(
            FinancialSummaryService::programTotals($user, null, $tahunAnggaranId),
            0,
            6,
        );
        $trenBulanan = $this->monthlyTrend($user, $tahunAnggaran);
        $penerimaanTarget = $this->penerimaanTarget($user, $tahunAnggaranId);

        $topOpd = $isAdmin
            ? Opd::query()
                ->withSum('pengeluarans as total_realisasi_pengeluaran', 'jumlah')
                ->orderByDesc('total_realisasi_pengeluaran')
                ->take(10)
                ->get()
            : collect();

        $kasPerSumberDana = $isAdmin
            ? $this->adminKasPerSumberDana()
            : $this->kasService->perSumberDana((int) $user->opd_id, $user);

        $recentPermintaan = FinancialSummaryService::applyOpd(PermintaanDana::query()->with('opd'), $user)
            ->latest()
            ->take(8)
            ->get();

        $recentTransfer = FinancialSummaryService::applyOpd(
            TransferDana::query()->with(['sumberDanaPengirim', 'sumberDanaPenerima']),
            $user,
        )->latest()->take(5)->get();

        $kuotaPersen = $this->kasService->kuotaPersen();

        return view('dashboard.index', compact(
            'tahunAnggaran', 'budget', 'kas', 'permintaanCounts', 'trenBulanan',
            'penerimaanTarget', 'programTotals', 'topOpd', 'kasPerSumberDana',
            'recentPermintaan', 'recentTransfer', 'kuotaPersen', 'isAdmin'
        ));
    }

    /**
     * Total penerimaan target within the user's scope for the active year.
     */
    private function penerimaanTarget(?User $user, ?int $tahunAnggaranId): float
    {
        $query = FinancialSummaryService::applyOpd(Penerimaan::query(), $user);

        if ($tahunAnggaranId !== null) {
            $query->where('tahun_anggaran_id', $tahunAnggaranId);
        }

        return (float) $query->sum('target');
    }

    /**
     * Monthly penerimaan vs pengeluaran series for the active fiscal year,
     * always 12 months so the chart never has gaps.
     *
     * @return array{labels: array<int, string>, penerimaan: array<int, float>, pengeluaran: array<int, float>}
     */
    private function monthlyTrend(?User $user, ?TahunAnggaran $tahunAnggaran): array
    {
        $opdId = FinancialSummaryService::opdScope($user);
        $year = $tahunAnggaran?->tahun !== null ? (int) $tahunAnggaran->tahun : null;

        $monthExpression = fn (string $column): string => DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', {$column}) AS INTEGER)"
            : "MONTH({$column})";

        $penerimaanByMonth = DB::table('transaksi_penerimaans as t')
            ->join('penerimaans as p', 'p.id', '=', 't.penerimaan_id')
            ->when($opdId !== null, fn ($q) => $q->where('p.opd_id', $opdId))
            ->when($year !== null, fn ($q) => $q->whereYear('t.tanggal', $year))
            ->selectRaw($monthExpression('t.tanggal').' as bulan')
            ->selectRaw('COALESCE(SUM(t.realisasi), 0) as total')
            ->groupByRaw($monthExpression('t.tanggal'))
            ->pluck('total', 'bulan');

        $pengeluaranByMonth = DB::table('pengeluarans')
            ->when($opdId !== null, fn ($q) => $q->where('opd_id', $opdId))
            ->when($year !== null, fn ($q) => $q->whereYear('tanggal', $year))
            ->selectRaw($monthExpression('tanggal').' as bulan')
            ->selectRaw('COALESCE(SUM(jumlah), 0) as total')
            ->groupByRaw($monthExpression('tanggal'))
            ->pluck('total', 'bulan');

        $penerimaan = [];
        $pengeluaran = [];

        for ($month = 1; $month <= 12; $month++) {
            $penerimaan[] = (float) ($penerimaanByMonth[$month] ?? 0);
            $pengeluaran[] = (float) ($pengeluaranByMonth[$month] ?? 0);
        }

        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'penerimaan' => $penerimaan,
            'pengeluaran' => $pengeluaran,
        ];
    }

    /**
     * Cash movement per sumber dana across every OPD (admin view).
     *
     * @return array<int, array{sumber_dana_id: int, nama: string, masuk: float, keluar: float, saldo: float}>
     */
    private function adminKasPerSumberDana(): array
    {
        $masuk = DB::table('transaksi_penerimaans')
            ->whereNotNull('sumber_dana_id')
            ->selectRaw('sumber_dana_id, COALESCE(SUM(realisasi), 0) as total')
            ->groupBy('sumber_dana_id')
            ->pluck('total', 'sumber_dana_id');

        $keluar = DB::table('pengeluarans')
            ->whereNotNull('sumber_dana_id')
            ->selectRaw('sumber_dana_id, COALESCE(SUM(jumlah), 0) as total')
            ->groupBy('sumber_dana_id')
            ->pluck('total', 'sumber_dana_id');

        return SumberDana::query()
            ->orderBy('nama_sumber_dana')
            ->get(['id', 'nama_sumber_dana'])
            ->map(function (SumberDana $sumberDana) use ($masuk, $keluar): array {
                $masukTotal = (float) ($masuk[$sumberDana->id] ?? 0);
                $keluarTotal = (float) ($keluar[$sumberDana->id] ?? 0);

                return [
                    'sumber_dana_id' => (int) $sumberDana->id,
                    'nama' => (string) $sumberDana->nama_sumber_dana,
                    'masuk' => round($masukTotal, 2),
                    'keluar' => round($keluarTotal, 2),
                    'saldo' => round($masukTotal - $keluarTotal, 2),
                ];
            })
            ->all();
    }
}
