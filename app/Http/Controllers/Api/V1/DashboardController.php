<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\PermintaanDana;
use App\Services\FinancialSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    /**
     * High-level financial snapshot for the authenticated user's OPD scope.
     * All totals come from aggregate SQL, never from loaded rows.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $opdId = $this->requestedOpdId($request);

        $budget = FinancialSummaryService::budgetTotals($user, $opdId);
        $cash = FinancialSummaryService::cashTotals($user, $opdId);

        return $this->success([
            'tahun_anggaran' => FinancialSummaryService::activeTahunAnggaran()?->tahun,
            'total_pagu' => $budget['pagu'],
            'total_realisasi' => $budget['realisasi'],
            'total_available' => $budget['available'],
            'total_commit' => $budget['commit'],
            'total_penerimaan' => FinancialSummaryService::revenueTotal($user, $opdId),
            'total_pengeluaran' => FinancialSummaryService::expenditureTotal($user, $opdId),
            'saldo_kas' => $cash['saldo_kas'],
            'permintaan_dana' => FinancialSummaryService::permintaanDanaCounts($user, $opdId),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function budget(Request $request): JsonResponse
    {
        $user = $request->user();
        $opdId = $this->requestedOpdId($request);
        $budget = FinancialSummaryService::budgetTotals($user, $opdId);
        $budget['percentage'] = FinancialSummaryService::percentage($budget['realisasi'], $budget['pagu']);

        return $this->success($budget);
    }

    public function revenue(Request $request): JsonResponse
    {
        $user = $request->user();
        $opdId = $this->requestedOpdId($request);

        [$from, $to] = $this->dateRange($request);

        $total = FinancialSummaryService::revenueTotal($user, $opdId, $from, $to);

        return $this->success([
            'total_penerimaan' => $total,
            'tanggal_dari' => $from,
            'tanggal_sampai' => $to,
        ]);
    }

    public function expenditure(Request $request): JsonResponse
    {
        $user = $request->user();
        $opdId = $this->requestedOpdId($request);

        [$from, $to] = $this->dateRange($request);

        $total = FinancialSummaryService::expenditureTotal($user, $opdId, $from, $to);

        return $this->success([
            'total_pengeluaran' => $total,
            'tanggal_dari' => $from,
            'tanggal_sampai' => $to,
        ]);
    }

    public function cash(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success(FinancialSummaryService::cashTotals($user, $this->requestedOpdId($request)));
    }

    public function programs(Request $request): JsonResponse
    {
        $user = $request->user();

        $programs = FinancialSummaryService::programTotals($user, $this->requestedOpdId($request));

        return $this->success($programs);
    }

    /**
     * Recent activity: latest permintaan dana within the user's scope.
     */
    public function activity(Request $request): JsonResponse
    {
        $user = $request->user();
        $opdId = $this->requestedOpdId($request);

        $permintaan = PermintaanDana::query()
            ->with('opd')
            ->when($opdId !== null, fn ($q) => $q->where('opd_id', $opdId))
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get(['id', 'nomor_permintaan', 'opd_id', 'jumlah', 'status', 'created_at']);

        return $this->success([
            'recent_permintaan' => $permintaan->map(fn ($p) => [
                'id' => $p->id,
                'nomor_permintaan' => $p->nomor_permintaan,
                'opd' => $p->opd?->nama,
                'jumlah' => (float) $p->jumlah,
                'status' => $p->status,
            ])->all(),
        ]);
    }

    /**
     * Explicit opd_id filter is honored for admins; OPD users are always
     * locked to their own OPD.
     */
    private function requestedOpdId(Request $request): ?int
    {
        $user = $request->user();

        if ($user !== null && ! $user->isAdmin()) {
            return (int) $user->opd_id;
        }

        $opdId = $request->input('opd_id');

        return $opdId !== null ? (int) $opdId : null;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function dateRange(Request $request): array
    {
        $from = $request->input('tanggal_dari');
        $to = $request->input('tanggal_sampai');

        return [
            is_string($from) && $from !== '' ? $from : null,
            is_string($to) && $to !== '' ? $to : null,
        ];
    }
}
