<?php

namespace App\Http\Controllers;

use App\Models\PermintaanDana;
use App\Models\Persetujuan;
use Illuminate\Http\Request;

class PersetujuanController extends Controller
{
    public function index(Request $request)
    {
        $permintaanDanas = $this->applyOpdScope(PermintaanDana::with(['opd', 'persetujuans']), $request->user())
            ->where('status', 'menunggu')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalMenunggu = $permintaanDanas->count();
        $totalMenungguNilai = $permintaanDanas->sum('jumlah');

        return view('persetujuan.index', compact(
            'permintaanDanas', 'totalMenunggu', 'totalMenungguNilai'
        ));
    }

    public function setujui(PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());

        if ($permintaanDana->status !== 'menunggu') {
            return back()->withErrors(['status' => 'Permintaan ini tidak dalam status menunggu.']);
        }

        $permintaanDana->sumberDana?->realize($permintaanDana->jumlah);

        $permintaanDana->update([
            'status' => 'disetujui',
            'tanggal_disetujui' => now(),
        ]);

        Persetujuan::create([
            'permintaan_dana_id' => $permintaanDana->id,
            'user_id' => auth()->id(),
            'keputusan' => 'disetujui',
            'catatan' => 'Disetujui oleh '.auth()->user()->name,
        ]);

        return back()->with('success', 'Permintaan dana berhasil disetujui.');
    }

    public function tolak(PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());

        if ($permintaanDana->status !== 'menunggu') {
            return back()->withErrors(['status' => 'Permintaan ini tidak dalam status menunggu.']);
        }

        $permintaanDana->sumberDana?->releaseCommit($permintaanDana->jumlah);

        $permintaanDana->update([
            'status' => 'ditolak',
        ]);

        Persetujuan::create([
            'permintaan_dana_id' => $permintaanDana->id,
            'user_id' => auth()->id(),
            'keputusan' => 'ditolak',
            'catatan' => 'Ditolak oleh '.auth()->user()->name,
        ]);

        return back()->with('success', 'Permintaan dana ditolak.');
    }
}
