<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\TahunAnggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunAnggaranController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $tahunAnggarans = TahunAnggaran::orderBy('tahun', 'desc')->get();

        return view('tahun-anggaran.index', compact('tahunAnggarans'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'tahun' => 'required|string|size:4|unique:tahun_anggarans,tahun',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        DB::transaction(function () use ($request) {
            $tahun = TahunAnggaran::create([
                'tahun' => $request->tahun,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'status' => 'open',
                'is_active' => false,
            ]);

            AuditLog::log('created', $tahun, [], $tahun->toArray());
        });

        return back()->with('success', 'Tahun anggaran berhasil ditambahkan.');
    }

    public function update(Request $request, TahunAnggaran $tahunAnggaran)
    {
        $this->authorizeAdmin();

        $request->validate([
            'status' => 'required|in:open,closed',
        ]);

        DB::transaction(function () use ($request, $tahunAnggaran) {
            $old = $tahunAnggaran->toArray();

            if ($request->status === 'closed') {
                $tahunAnggaran->close();
            } else {
                $tahunAnggaran->open();
            }

            AuditLog::log(
                $request->status === 'closed' ? 'period_closed' : 'period_reopened',
                $tahunAnggaran,
                $old,
                $tahunAnggaran->fresh()->toArray()
            );
        });

        return back()->with('success', 'Status tahun anggaran berhasil diperbarui.');
    }

    public function activate(TahunAnggaran $tahunAnggaran)
    {
        $this->authorizeAdmin();

        DB::transaction(function () use ($tahunAnggaran) {
            $old = $tahunAnggaran->toArray();
            $tahunAnggaran->activate();
            AuditLog::log('updated', $tahunAnggaran, $old, $tahunAnggaran->fresh()->toArray());
        });

        return back()->with('success', "Tahun anggaran {$tahunAnggaran->tahun} diaktifkan.");
    }

    public function destroy(TahunAnggaran $tahunAnggaran)
    {
        $this->authorizeAdmin();

        if ($tahunAnggaran->is_active) {
            return back()->withErrors(['tahun' => 'Tahun anggaran yang aktif tidak dapat dihapus.']);
        }

        AuditLog::log('deleted', $tahunAnggaran, $tahunAnggaran->toArray(), []);
        $tahunAnggaran->delete();

        return back()->with('success', 'Tahun anggaran berhasil dihapus.');
    }

    protected function authorizeAdmin(): void
    {
        if (! request()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengelola tahun anggaran.');
        }
    }
}
