<?php

namespace App\Http\Controllers;

use App\Models\PermintaanDana;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapPermintaanDanaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = PermintaanDana::with(['opd', 'sumberDana', 'kegiatan']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query = $this->applyOpdScope($query, $user);
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));

        $permintaanDanas = $query->orderBy('created_at', 'desc')->get();

        $totalPermintaan = $permintaanDanas->sum('jumlah');
        $totalDisetujui = $permintaanDanas->where('status', 'disetujui')->sum('jumlah');
        $totalDitolak = $permintaanDanas->where('status', 'ditolak')->sum('jumlah');
        $totalMenunggu = $permintaanDanas->where('status', 'menunggu')->sum('jumlah');

        $opds = $this->userOpds($user);
        $filters = $request->only(['opd_id', 'status', 'sumber_dana_id', 'tanggal_dari', 'tanggal_sampai']);

        return view('rekap-permintaan-dana.index', compact(
            'permintaanDanas', 'totalPermintaan', 'totalDisetujui',
            'totalDitolak', 'totalMenunggu', 'opds', 'filters'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();

        $query = PermintaanDana::with(['opd', 'sumberDana', 'kegiatan']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query = $this->applyOpdScope($query, $user);
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));

        $permintaanDanas = $query->orderBy('created_at', 'desc')->get();

        $filename = 'rekap-permintaan-dana-'.now()->format('Y-m-d').'.csv';

        return response()->stream(function () use ($permintaanDanas) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Nomor Permintaan', 'Tanggal', 'OPD', 'Sumber Dana', 'Jumlah', 'Status',
            ]);

            foreach ($permintaanDanas as $idx => $item) {
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

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
