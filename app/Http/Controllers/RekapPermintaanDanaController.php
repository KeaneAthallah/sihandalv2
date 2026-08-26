<?php

namespace App\Http\Controllers;

use App\Models\PermintaanDana;
use Illuminate\Http\Request;

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
}
