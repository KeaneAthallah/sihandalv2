<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransferDanaRequest;
use App\Http\Requests\UpdateTransferDanaRequest;
use App\Models\TransferDana;
use Illuminate\Http\Request;

class TransferDanaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $transferDanas = $this->applyOpdScope(TransferDana::with('opd'), $user)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalTransfer = $transferDanas->sum('jumlah');
        $totalSelesai = $transferDanas->where('status', 'selesai')->sum('jumlah');
        $totalDiproses = $transferDanas->where('status', 'diproses')->sum('jumlah');

        $opds = $this->userOpds($user);

        return view('transfer-dana.index', compact(
            'transferDanas', 'totalTransfer', 'totalSelesai',
            'totalDiproses', 'opds'
        ));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());

        return view('transfer-dana.create', compact('opds'));
    }

    public function store(StoreTransferDanaRequest $request)
    {
        $data = $request->validated();

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $lastNumber = TransferDana::whereYear('created_at', now()->year)->count() + 1;
        $data['nomor_transfer'] = 'TF-'.str_pad($lastNumber, 4, '0', STR_PAD_LEFT).'/'.now()->format('Y');
        $data['status'] = 'draft';

        TransferDana::create($data);

        return back()->with('success', 'Transfer dana berhasil dibuat.');
    }

    public function edit(TransferDana $transferDana)
    {
        $this->authorizeOpdRecord($transferDana, request()->user());
        $opds = $this->userOpds(request()->user());

        return view('transfer-dana.edit', compact('transferDana', 'opds'));
    }

    public function update(UpdateTransferDanaRequest $request, TransferDana $transferDana)
    {
        $this->authorizeOpdRecord($transferDana, $request->user());
        $data = $request->validated();

        if (isset($data['status']) && $data['status'] === 'selesai' && $transferDana->status !== 'selesai') {
            $data['tanggal_selesai'] = now();
        }

        $transferDana->update($data);

        return back()->with('success', 'Transfer dana berhasil diperbarui.');
    }

    public function destroy(TransferDana $transferDana)
    {
        $this->authorizeOpdRecord($transferDana, request()->user());
        $transferDana->delete();

        return back()->with('success', 'Transfer dana berhasil dihapus.');
    }
}
