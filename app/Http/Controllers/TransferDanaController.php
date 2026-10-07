<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransferDanaRequest;
use App\Http\Requests\UpdateTransferDanaRequest;
use App\Models\SumberDana;
use App\Models\TransferDana;
use App\Services\PermintaanDanaService;
use App\Services\TransferDanaService;
use Illuminate\Http\Request;
use RuntimeException;

class TransferDanaController extends Controller
{
    public function __construct(
        private readonly PermintaanDanaService $workflow,
        private readonly TransferDanaService $transferDanaService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $transferQuery = $this->applyOpdScope(TransferDana::with(['opd', 'sumberDanaPengirim', 'sumberDanaPenerima']), $user)
            ->orderBy('created_at', 'desc');

        $totalTransfer = (clone $transferQuery)->sum('jumlah');
        $totalSelesai = (clone $transferQuery)->where('status', 'selesai')->sum('jumlah');
        $totalDiproses = (clone $transferQuery)->where('status', 'diproses')->sum('jumlah');
        $totalSelesaiCount = (clone $transferQuery)->where('status', 'selesai')->count();
        $totalDiprosesCount = (clone $transferQuery)->where('status', 'diproses')->count();

        $transferDanas = $transferQuery->paginate(15);

        $opds = $this->userOpds($user);

        return view('transfer-dana.index', compact(
            'transferDanas', 'totalTransfer', 'totalSelesai',
            'totalDiproses', 'totalSelesaiCount', 'totalDiprosesCount', 'opds'
        ));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();

        return view('transfer-dana.create', compact('opds', 'sumberDanas'));
    }

    public function store(StoreTransferDanaRequest $request)
    {
        $data = $request->validated();

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $data['nomor_transfer'] = $this->workflow->nextNomorTransfer();
        $data['status'] = 'draft';

        TransferDana::create($data);

        return back()->with('success', 'Transfer dana berhasil dibuat.');
    }

    public function edit(TransferDana $transferDana)
    {
        $this->authorizeOpdRecord($transferDana, request()->user());
        $opds = $this->userOpds(request()->user());
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();

        return view('transfer-dana.edit', compact('transferDana', 'opds', 'sumberDanas'));
    }

    public function update(UpdateTransferDanaRequest $request, TransferDana $transferDana)
    {
        $this->authorizeOpdRecord($transferDana, $request->user());

        if ($transferDana->status === 'selesai') {
            return back()->withErrors(['status' => 'Transfer yang sudah selesai tidak dapat diubah.']);
        }

        $data = $request->validated();

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        // Transisi ke "selesai": kas pengirim wajib mencukupi
        // (validasi pra-cek pada request, pengecekan dengan
        // kunci baris pada layanan) dan penyelesaian hanya
        // oleh admin.
        if (isset($data['status']) && $data['status'] === 'selesai' && $transferDana->status !== 'selesai') {
            if (! $request->user()->isAdmin()) {
                return back()->withErrors(['status' => 'Hanya admin yang dapat menyelesaikan transfer dana.']);
            }

            try {
                $this->transferDanaService->selesaikan($transferDana);
            } catch (RuntimeException $e) {
                return back()->withErrors(['jumlah' => $e->getMessage()]);
            }

            return back()->with('success', 'Transfer dana berhasil diselesaikan.');
        }

        $transferDana->update($data);

        return back()->with('success', 'Transfer dana berhasil diperbarui.');
    }

    public function destroy(TransferDana $transferDana)
    {
        $this->authorizeOpdRecord($transferDana, request()->user());

        if ($transferDana->status === 'selesai') {
            return back()->withErrors(['status' => 'Transfer yang sudah selesai tidak dapat dihapus.']);
        }

        $transferDana->delete();

        return back()->with('success', 'Transfer dana berhasil dihapus.');
    }
}
