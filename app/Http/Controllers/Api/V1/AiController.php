<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Belanja;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\TransferDana;
use App\Services\FinancialSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-oriented, structured factual endpoints designed for AI/LLM consumers.
 * They obey exactly the same auth + OPD scoping as every other endpoint and
 * only expose data that is derivable from the SIHANDAL domain.
 */
class AiController extends ApiController
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $opdId = $this->requestedOpdId($request);

        $budget = FinancialSummaryService::budgetTotals($user, $opdId);
        $cash = FinancialSummaryService::cashTotals($user, $opdId);
        $opd = $opdId !== null ? Opd::find($opdId) : null;

        return $this->success([
            'fiscal_year' => FinancialSummaryService::activeTahunAnggaran()?->tahun,
            'opd' => $opd !== null ? ['id' => $opd->id, 'name' => $opd->nama] : null,
            'budget' => [
                'pagu' => $budget['pagu'],
                'realisasi' => $budget['realisasi'],
                'commit' => $budget['commit'],
                'available' => $budget['available'],
                'percentage' => FinancialSummaryService::percentage($budget['realisasi'], $budget['pagu']),
            ],
            'revenue' => FinancialSummaryService::revenueTotal($user, $opdId),
            'expenditure' => FinancialSummaryService::expenditureTotal($user, $opdId),
            'cash' => $cash,
            'fund_requests' => FinancialSummaryService::permintaanDanaCounts($user, $opdId),
        ]);
    }

    public function budgetSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $opdId = $this->requestedOpdId($request);
        $budget = FinancialSummaryService::budgetTotals($user, $opdId);
        $budget['percentage'] = FinancialSummaryService::percentage($budget['realisasi'], $budget['pagu']);
        $budget['commit_percentage'] = FinancialSummaryService::percentage($budget['commit'], $budget['pagu']);

        return $this->success($budget);
    }

    public function revenueSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $opdId = $this->requestedOpdId($request);

        $total = FinancialSummaryService::revenueTotal($user, $opdId);

        // Revenue realization against the sum of Penerimaan targets.
        $targets = Penerimaan::query()
            ->when($opdId !== null, fn ($q) => $q->where('opd_id', $opdId))
            ->sum('target');

        return $this->success([
            'target' => (float) $targets,
            'realisasi' => $total,
            'percentage' => FinancialSummaryService::percentage($total, (float) $targets),
        ]);
    }

    public function expenditureSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $opdId = $this->requestedOpdId($request);

        $total = FinancialSummaryService::expenditureTotal($user, $opdId);
        $budget = FinancialSummaryService::budgetTotals($user, $opdId);

        return $this->success([
            'realisasi' => $total,
            'pagu' => $budget['pagu'],
            'percentage_of_budget' => FinancialSummaryService::percentage($total, $budget['pagu']),
        ]);
    }

    public function cashSummary(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success(FinancialSummaryService::cashTotals($user, $this->requestedOpdId($request)));
    }

    public function programSummary(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success(FinancialSummaryService::programTotals($user, $this->requestedOpdId($request)));
    }

    /**
     * Informational alerts derived strictly from existing SIHANDAL data rules:
     * low budget availability, high realization, pending fund requests, pending
     * approvals, completed transfers. No financial records are modified.
     */
    public function financialAlerts(Request $request): JsonResponse
    {
        $user = $request->user();
        $opdId = $this->requestedOpdId($request);

        $alerts = [];
        $budget = FinancialSummaryService::budgetTotals($user, $opdId);

        // Low budget availability (< 10% of pagu remains).
        if ($budget['pagu'] > 0) {
            $availablePct = FinancialSummaryService::percentage($budget['available'], $budget['pagu']);
            if ($availablePct < 10) {
                $alerts[] = [
                    'type' => 'low_budget_availability',
                    'severity' => 'warning',
                    'message' => "Sisa pagu tersedia hanya {$availablePct}% dari total pagu.",
                    'value' => $availablePct,
                ];
            }

            // High realization (> 90% of pagu realized).
            $realizationPct = FinancialSummaryService::percentage($budget['realisasi'], $budget['pagu']);
            if ($realizationPct > 90) {
                $alerts[] = [
                    'type' => 'high_realization',
                    'severity' => 'info',
                    'message' => "Realisasi belanja telah mencapai {$realizationPct}% dari pagu.",
                    'value' => $realizationPct,
                ];
            }
        }

        // Pending fund requests / approvals.
        $counts = FinancialSummaryService::permintaanDanaCounts($user, $opdId);
        if ($counts['menunggu'] > 0) {
            $alerts[] = [
                'type' => 'pending_fund_requests',
                'severity' => 'info',
                'message' => "{$counts['menunggu']} permintaan dana menunggu persetujuan.",
                'value' => $counts['menunggu'],
            ];
        }
        if ($counts['draft'] > 0) {
            $alerts[] = [
                'type' => 'draft_fund_requests',
                'severity' => 'info',
                'message' => "{$counts['draft']} permintaan dana masih draft.",
                'value' => $counts['draft'],
            ];
        }

        // Belanja rows fully committed or realized (nothing left available).
        $fullyUsed = Belanja::query()
            ->when($opdId !== null, fn ($q) => $q->where('opd_id', $opdId))
            ->get()
            ->filter(fn (Belanja $b) => $b->pagu > 0 && $b->availablePagu() <= 0)
            ->count();

        if ($fullyUsed > 0) {
            $alerts[] = [
                'type' => 'exhausted_budget_lines',
                'severity' => 'warning',
                'message' => "{$fullyUsed} baris belanja telah menggunakan seluruh pagunya.",
                'value' => $fullyUsed,
            ];
        }

        // Transfers in progress.
        $diproses = TransferDana::query()
            ->when($opdId !== null, fn ($q) => $q->where('opd_id', $opdId))
            ->where('status', 'diproses')
            ->count();

        if ($diproses > 0) {
            $alerts[] = [
                'type' => 'transfers_in_progress',
                'severity' => 'info',
                'message' => "{$diproses} transfer dana sedang diproses.",
                'value' => $diproses,
            ];
        }

        return $this->success($alerts);
    }

    private function requestedOpdId(Request $request): ?int
    {
        $user = $request->user();

        if ($user !== null && ! $user->isAdmin()) {
            return (int) $user->opd_id;
        }

        $opdId = $request->input('opd_id');

        return $opdId !== null ? (int) $opdId : null;
    }
}
