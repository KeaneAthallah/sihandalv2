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
        $permintaanDanas = $this->applyOpdScope(PermintaanDana::with('opd'), $user)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPermintaan = $permintaanDanas->sum('jumlah');
        $totalDisetujui = $permintaanDanas->where('status', 'disetujui')->sum('jumlah');
        $totalDitolak = $permintaanDanas->where('status', 'ditolak')->sum('jumlah');
        $totalMenunggu = $permintaanDanas->where('status', 'menunggu')->sum('jumlah');

        $opds = $this->userOpds($user);

        return view('rekap-permintaan-dana.index', compact(
            'permintaanDanas', 'totalPermintaan', 'totalDisetujui',
            'totalDitolak', 'totalMenunggu', 'opds'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $permintaanDanas = $this->applyOpdScope(PermintaanDana::with('opd'), $user)
            ->orderBy('created_at', 'desc')
            ->get();

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
                    $item->sumber_dana,
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
