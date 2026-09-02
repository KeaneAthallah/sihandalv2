<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Pengeluaran;
use App\Models\SumberDana;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Pengeluaran::with(['opd', 'kegiatan', 'sumberDana', 'rekening']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query = $this->applyOpdScope($query, $user);
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        $query->when($request->filled('kegiatan_id'), fn ($q) => $q->where('kegiatan_id', $request->input('kegiatan_id')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));

        $pengeluarans = $query->orderBy('tanggal', 'desc')->get();

        $totalAnggaran = $pengeluarans->sum('anggaran');
        $totalRealisasi = $pengeluarans->sum('realisasi');
        $persentase = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 1) : 0;

        $opds = $this->userOpds($user);
        $kegiatans = Kegiatan::orderBy('kode_kegiatan')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();
        $filters = $request->only(['opd_id', 'kegiatan_id', 'sumber_dana_id', 'tanggal_dari', 'tanggal_sampai']);

        return view('laporan-pengeluaran.index', compact(
            'pengeluarans', 'totalAnggaran', 'totalRealisasi', 'persentase',
            'opds', 'kegiatans', 'sumberDanas', 'filters'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();

        $query = Pengeluaran::with(['opd', 'kegiatan', 'sumberDana', 'rekening']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query = $this->applyOpdScope($query, $user);
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        $query->when($request->filled('kegiatan_id'), fn ($q) => $q->where('kegiatan_id', $request->input('kegiatan_id')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));

        $pengeluarans = $query->orderBy('tanggal', 'desc')->get();

        $filename = 'laporan-pengeluaran-'.now()->format('Y-m-d').'.csv';

        return response()->stream(function () use ($pengeluarans) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Tanggal', 'OPD', 'Kegiatan', 'Anggaran', 'Realisasi', 'Persentase (%)',
            ]);

            foreach ($pengeluarans as $idx => $item) {
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

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
