<?php

namespace App\Http\Controllers;

use App\Models\PermintaanDana;
use App\Services\PermintaanDanaService;
use Illuminate\Http\Request;

class PersetujuanController extends Controller
{
    public function __construct(private readonly PermintaanDanaService $workflow) {}

    public function index(Request $request)
    {
        $permintaanQuery = $this->applyOpdScope(PermintaanDana::with(['opd', 'persetujuans', 'sumberDana']), $request->user())
            ->where('status', 'menunggu')
            ->orderBy('created_at', 'desc');

        $totalMenungguNilai = (clone $permintaanQuery)->sum('jumlah');
        $permintaanDanas = $permintaanQuery->paginate(15);

        $totalMenunggu = $permintaanDanas->total();

        return view('persetujuan.index', compact(
            'permintaanDanas', 'totalMenunggu', 'totalMenungguNilai'
        ));
    }

    public function setujui(PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());

        if (! request()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menyetujui permintaan dana.');
        }

        try {
            $this->workflow->approve($permintaanDana, request()->user());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Permintaan dana berhasil disetujui.');
    }

    public function tolak(PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());

        if (! request()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menolak permintaan dana.');
        }

        try {
            $this->workflow->reject($permintaanDana, request()->user());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Permintaan dana ditolak.');
    }
}
