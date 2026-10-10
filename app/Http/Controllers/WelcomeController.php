<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\SumberDana;
use App\Services\FinancialSummaryService;
use Illuminate\Contracts\View\View;

class WelcomeController extends Controller
{
    /**
     * Public landing page with live, all-OPD figures.
     */
    public function index(): View
    {
        $activeYear = FinancialSummaryService::activeTahunAnggaran();

        $budget = FinancialSummaryService::budgetTotals(null, null, $activeYear?->id);
        $pagu = $budget['pagu'];
        $realisasi = $budget['realisasi'];

        $dateFrom = $activeYear?->tanggal_mulai?->toDateString();
        $dateTo = $activeYear?->tanggal_selesai?->toDateString();

        return view('welcome', [
            'sumberDanaCount' => SumberDana::count(),
            'opdCount' => Opd::count(),
            'totalPagu' => $pagu,
            'totalRealisasi' => $realisasi,
            'persenRealisasi' => FinancialSummaryService::percentage($realisasi, $pagu),
            'totalPenerimaan' => FinancialSummaryService::revenueTotal(null, null, $dateFrom, $dateTo),
            'totalPengeluaran' => FinancialSummaryService::expenditureTotal(null, null, $dateFrom, $dateTo),
            'tahunAnggaran' => $activeYear?->tahun,
        ]);
    }
}
