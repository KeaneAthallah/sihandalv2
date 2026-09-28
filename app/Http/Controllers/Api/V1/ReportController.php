<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PenerimaanResource;
use App\Http\Resources\PengeluaranResource;
use App\Http\Resources\PermintaanDanaResource;
use App\Http\Resources\PosisiKasResource;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\PosisiKas;
use App\Services\FinancialSummaryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Read-only report endpoints mirroring the web laporan controllers. Each
 * report returns paginated rows plus summary totals in meta, scoped to the
 * user's OPD. CSV export variants stream the same dataset.
 */
class ReportController extends ApiController
{
    public function penerimaan(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $this->penerimaanQuery($request, $user);

        $all = (clone $query)->orderBy('target', 'desc')->get();
        $totalTarget = $all->sum('target');
        $totalRealisasi = $all->sum('realisasi');

        $perPage = $this->perPage($request);
        $data = $perPage === null
            ? PenerimaanResource::collection($all->values())->resolve()
            : $this->paginated($query->orderBy('target', 'desc')->paginate($perPage), PenerimaanResource::class)->getData(true);

        if ($perPage === null) {
            return $this->withMeta($data, [
                'total_target' => (float) $totalTarget,
                'total_realisasi' => (float) $totalRealisasi,
                'persentase' => FinancialSummaryService::percentage((float) $totalRealisasi, (float) $totalTarget),
            ]);
        }

        $payload = $data;
        $payload['meta']['total_target'] = (float) $totalTarget;
        $payload['meta']['total_realisasi'] = (float) $totalRealisasi;
        $payload['meta']['persentase'] = FinancialSummaryService::percentage((float) $totalRealisasi, (float) $totalTarget);

        return response()->json($payload);
    }

    public function pengeluaran(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $this->pengeluaranQuery($request, $user);

        $totalAnggaran = (float) (clone $query)->sum('anggaran');
        $totalRealisasi = (float) (clone $query)->sum('realisasi');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            $data = PengeluaranResource::collection($query->orderBy('tanggal', 'desc')->get())->resolve();

            return $this->withMeta($data, [
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'persentase' => FinancialSummaryService::percentage($totalRealisasi, $totalAnggaran),
            ]);
        }

        $payload = $this->paginated($query->orderBy('tanggal', 'desc')->paginate($perPage), PengeluaranResource::class)->getData(true);
        $payload['meta']['total_anggaran'] = $totalAnggaran;
        $payload['meta']['total_realisasi'] = $totalRealisasi;
        $payload['meta']['persentase'] = FinancialSummaryService::percentage($totalRealisasi, $totalAnggaran);

        return response()->json($payload);
    }

    public function posisiKas(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $this->posisiKasQuery($request, $user);

        $totalSaldoAwal = (float) (clone $query)->sum('saldo_awal');
        $totalPenerimaan = (float) (clone $query)->sum('penerimaan');
        $totalPengeluaran = (float) (clone $query)->sum('pengeluaran');
        $totalSaldoAkhir = (float) (clone $query)->sum('saldo_akhir');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            $data = PosisiKasResource::collection($query->orderBy('tanggal', 'desc')->get())->resolve();

            return $this->withMeta($data, [
                'total_saldo_awal' => $totalSaldoAwal,
                'total_penerimaan' => $totalPenerimaan,
                'total_pengeluaran' => $totalPengeluaran,
                'total_saldo_akhir' => $totalSaldoAkhir,
            ]);
        }

        $payload = $this->paginated($query->orderBy('tanggal', 'desc')->paginate($perPage), PosisiKasResource::class)->getData(true);
        $payload['meta']['total_saldo_awal'] = $totalSaldoAwal;
        $payload['meta']['total_penerimaan'] = $totalPenerimaan;
        $payload['meta']['total_pengeluaran'] = $totalPengeluaran;
        $payload['meta']['total_saldo_akhir'] = $totalSaldoAkhir;

        return response()->json($payload);
    }

    public function permintaanDana(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $this->permintaanDanaQuery($request, $user);

        $totalPermintaan = (float) (clone $query)->sum('jumlah');
        $totalDisetujui = (float) (clone $query)->where('status', 'disetujui')->sum('jumlah');
        $totalDitolak = (float) (clone $query)->where('status', 'ditolak')->sum('jumlah');
        $totalMenunggu = (float) (clone $query)->where('status', 'menunggu')->sum('jumlah');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            $data = PermintaanDanaResource::collection($query->orderBy('created_at', 'desc')->get())->resolve();

            return $this->withMeta($data, [
                'total_permintaan' => $totalPermintaan,
                'total_disetujui' => $totalDisetujui,
                'total_ditolak' => $totalDitolak,
                'total_menunggu' => $totalMenunggu,
            ]);
        }

        $payload = $this->paginated($query->orderBy('created_at', 'desc')->paginate($perPage), PermintaanDanaResource::class)->getData(true);
        $payload['meta']['total_permintaan'] = $totalPermintaan;
        $payload['meta']['total_disetujui'] = $totalDisetujui;
        $payload['meta']['total_ditolak'] = $totalDitolak;
        $payload['meta']['total_menunggu'] = $totalMenunggu;

        return response()->json($payload);
    }

    // ------------------------------------------------------------------
    // CSV exports (streamed, same scoping/filters as the JSON reports)
    // ------------------------------------------------------------------

    public function exportPenerimaan(Request $request): StreamedResponse
    {
        $rows = $this->penerimaanQuery($request, $request->user())->orderBy('target', 'desc')->get();

        return $this->streamCsv('laporan-penerimaan', function ($handle) use ($rows): void {
            fputcsv($handle, ['No', 'OPD', 'Sumber Dana', 'Target', 'Realisasi', 'Persentase (%)', 'Selisih']);
            foreach ($rows as $idx => $item) {
                fputcsv($handle, [
                    $idx + 1,
                    $item->opd?->nama ?? '-',
                    $item->sumberDana?->nama_sumber_dana ?? $item->nama_sumber_dana ?? '-',
                    $item->target,
                    $item->realisasi,
                    $item->persentase,
                    (float) $item->realisasi - (float) $item->target,
                ]);
            }
        });
    }

    public function exportPengeluaran(Request $request): StreamedResponse
    {
        $rows = $this->pengeluaranQuery($request, $request->user())->orderBy('tanggal', 'desc')->get();

        return $this->streamCsv('laporan-pengeluaran', function ($handle) use ($rows): void {
            fputcsv($handle, ['No', 'Tanggal', 'OPD', 'Kegiatan', 'Anggaran', 'Realisasi', 'Persentase (%)']);
            foreach ($rows as $idx => $item) {
                fputcsv($handle, [
                    $idx + 1,
                    $item->tanggal?->format('d/m/Y') ?? '-',
                    $item->opd->nama ?? '-',
                    $item->kegiatan?->nama_kegiatan ?? $item->nama_kegiatan ?? '-',
                    $item->anggaran,
                    $item->realisasi,
                    $item->persentase,
                ]);
            }
        });
    }

    public function exportPosisiKas(Request $request): StreamedResponse
    {
        $rows = $this->posisiKasQuery($request, $request->user())->orderBy('tanggal', 'desc')->get();

        return $this->streamCsv('laporan-posisi-kas', function ($handle) use ($rows): void {
            fputcsv($handle, ['No', 'Tanggal', 'OPD', 'Rekening', 'Saldo Awal', 'Penerimaan', 'Pengeluaran', 'Saldo Akhir']);
            foreach ($rows as $idx => $item) {
                fputcsv($handle, [
                    $idx + 1,
                    $item->tanggal?->format('d/m/Y') ?? '-',
                    $item->opd->nama ?? '-',
                    $item->rekening->nama ?? '-',
                    $item->saldo_awal,
                    $item->penerimaan,
                    $item->pengeluaran,
                    $item->saldo_akhir,
                ]);
            }
        });
    }

    public function exportPermintaanDana(Request $request): StreamedResponse
    {
        $rows = $this->permintaanDanaQuery($request, $request->user())->orderBy('created_at', 'desc')->get();

        return $this->streamCsv('rekap-permintaan-dana', function ($handle) use ($rows): void {
            fputcsv($handle, ['No', 'Nomor Permintaan', 'Tanggal', 'OPD', 'Sumber Dana', 'Jumlah', 'Status']);
            foreach ($rows as $idx => $item) {
                fputcsv($handle, [
                    $idx + 1,
                    $item->nomor_permintaan,
                    $item->tanggal?->format('d/m/Y') ?? '-',
                    $item->opd->nama ?? '-',
                    $item->sumberDana?->nama_sumber_dana ?? $item->sumber_dana ?? '-',
                    $item->jumlah,
                    $item->status,
                ]);
            }
        });
    }

    // ------------------------------------------------------------------
    // Shared query builders (identical scoping to the web laporan controllers)
    // ------------------------------------------------------------------

    private function penerimaanQuery(Request $request, $user): Builder
    {
        $query = Penerimaan::query()->with(['opd', 'sumberDana', 'rekening']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id));
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        return $query
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereHas('transaksiPenerimaans', fn ($t) => $t->whereDate('tanggal', '>=', $request->input('tanggal_dari'))))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereHas('transaksiPenerimaans', fn ($t) => $t->whereDate('tanggal', '<=', $request->input('tanggal_sampai'))));
    }

    private function pengeluaranQuery(Request $request, $user): Builder
    {
        $query = Pengeluaran::query()->with(['opd', 'kegiatan', 'sumberDana', 'rekening']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id));
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        return $query
            ->when($request->filled('kegiatan_id'), fn ($q) => $q->where('kegiatan_id', $request->input('kegiatan_id')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));
    }

    private function posisiKasQuery(Request $request, $user): Builder
    {
        $query = PosisiKas::query()->with(['opd', 'rekening']);

        if (! $user->isAdmin()) {
            $query->where('opd_id', $user->opd_id);
        } elseif ($request->filled('opd_id')) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        return $query
            ->when($request->filled('rekening_id'), fn ($q) => $q->where('rekening_id', $request->input('rekening_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));
    }

    private function permintaanDanaQuery(Request $request, $user): Builder
    {
        $query = PermintaanDana::query()->with(['opd', 'sumberDana', 'kegiatan']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id));
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        return $query
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));
    }

    /**
     * Streams a CSV response with a BOM-free header and timestamped filename.
     *
     * @param  callable(resource): void  $writer
     */
    private function streamCsv(string $name, callable $writer): StreamedResponse
    {
        $filename = $name.'-'.now()->format('Y-m-d').'.csv';

        return response()->stream(function () use ($writer): void {
            $handle = fopen('php://output', 'w');
            $writer($handle);
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
