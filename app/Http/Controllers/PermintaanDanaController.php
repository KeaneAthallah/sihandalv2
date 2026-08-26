<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermintaanDanaRequest;
use App\Http\Requests\UpdatePermintaanDanaRequest;
use App\Models\PermintaanDana;
use App\Models\SumberDana;
use Illuminate\Http\Request;

class PermintaanDanaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permintaanDanas = $this->applyOpdScope(PermintaanDana::with('opd'), $user)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPermintaan = $permintaanDanas->sum('jumlah');
        $totalDisetujui = $permintaanDanas->where('status', 'disetujui')->sum('jumlah');
        $totalMenunggu = $permintaanDanas->where('status', 'menunggu')->sum('jumlah');

        $opds = $this->userOpds($user);

        return view('permintaan-dana.index', compact(
            'permintaanDanas', 'totalPermintaan', 'totalDisetujui',
            'totalMenunggu', 'opds'
        ));
    }

    public function create()
    {
        $user = request()->user();
        $opds = $this->userOpds($user);
        $sumberDanas = $this->applyOpdScope(SumberDana::query(), $user)
            ->orderBy('nama_sumber_dana')
            ->get();

        return view('permintaan-dana.create', compact('opds', 'sumberDanas'));
    }

    public function store(StorePermintaanDanaRequest $request)
    {
        $data = $request->validated();
        $sumberDana = SumberDana::findOrFail($data['sumber_dana_id']);

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $data['sumber_dana'] = $sumberDana->nama_sumber_dana;

        $lastNumber = PermintaanDana::whereYear('created_at', now()->year)->count() + 1;
        $data['nomor_permintaan'] = 'PD-'.str_pad($lastNumber, 4, '0', STR_PAD_LEFT).'/'.now()->format('Y');
        $data['status'] = 'draft';

        PermintaanDana::create($data);

        return back()->with('success', 'Permintaan dana berhasil dibuat sebagai draft.');
    }

    public function edit(PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());
        $user = request()->user();
        $opds = $this->userOpds($user);
        $sumberDanas = $this->applyOpdScope(SumberDana::query(), $user)
            ->orderBy('nama_sumber_dana')
            ->get();

        return view('permintaan-dana.edit', compact('permintaanDana', 'opds', 'sumberDanas'));
    }

    public function update(UpdatePermintaanDanaRequest $request, PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, $request->user());

        if (! in_array($permintaanDana->status, ['draft', 'ditolak'])) {
            return back()->withErrors(['status' => 'Hanya permintaan draft atau ditolak yang dapat diedit.']);
        }

        $data = $request->validated();
        $data['sumber_dana'] = SumberDana::findOrFail($data['sumber_dana_id'])->nama_sumber_dana;

        $permintaanDana->update($data);

        return back()->with('success', 'Permintaan dana berhasil diperbarui.');
    }

    public function destroy(PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());

        if (! in_array($permintaanDana->status, ['draft', 'ditolak'])) {
            return back()->withErrors(['status' => 'Hanya permintaan draft atau ditolak yang dapat dihapus.']);
        }

        $permintaanDana->delete();

        return back()->with('success', 'Permintaan dana berhasil dihapus.');
    }

    public function submit(PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());

        if ($permintaanDana->status !== 'draft') {
            return back()->withErrors(['status' => 'Hanya permintaan draft yang dapat diajukan.']);
        }

        $sumberDana = $permintaanDana->sumberDana;

        if ($sumberDana === null) {
            return back()->withErrors(['sumber_dana' => 'Sumber dana tidak ditemukan.']);
        }

        if ((float) $permintaanDana->jumlah > $sumberDana->availablePagu()) {
            return back()->withErrors(['jumlah' => 'Dana tidak mencukupi. Sisa pagu sumber dana: Rp '.number_format($sumberDana->availablePagu(), 0, ',', '.').'.']);
        }

        $permintaanDana->update([
            'status' => 'menunggu',
            'tanggal' => $permintaanDana->tanggal ?? now(),
        ]);

        $sumberDana->commit((float) $permintaanDana->jumlah);

        return back()->with('success', 'Permintaan dana berhasil diajukan. Dana sebesar Rp '.number_format($permintaanDana->jumlah, 0, ',', '.').' telah di-commit.');
    }
}
