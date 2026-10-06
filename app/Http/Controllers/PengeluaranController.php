<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesBudgetHierarchyCascade;
use App\Http\Requests\StorePengeluaranRequest;
use App\Http\Requests\UpdatePengeluaranRequest;
use App\Models\Kegiatan;
use App\Models\Pengeluaran;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengeluaranController extends Controller
{
    use ProvidesBudgetHierarchyCascade;

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
            ->when($request->filled('rekening_id'), fn ($q) => $q->where('rekening_id', $request->input('rekening_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));

        $pengeluarans = $query->orderBy('tanggal', 'desc')->paginate(15);

        $totalJumlah = (clone $query)->sum('jumlah');

        $opds = $this->userOpds($user);
        $kegiatans = Kegiatan::with('program')->orderBy('kode_kegiatan')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();
        $rekenings = Rekening::orderBy('kode')->get();
        $filters = $request->only(['opd_id', 'kegiatan_id', 'sumber_dana_id', 'rekening_id', 'tanggal_dari', 'tanggal_sampai']);

        return view('pengeluaran.index', compact(
            'pengeluarans', 'totalJumlah',
            'opds', 'kegiatans', 'sumberDanas', 'rekenings', 'filters'
        ));
    }

    public function create()
    {
        $user = request()->user();
        $opds = $this->userOpds($user);

        $programsByOpd = $this->programsByOpd();
        $kegiatansByProgram = $this->kegiatansByProgram($user);
        $subKegiatansByKegiatan = $this->subKegiatansByKegiatan($user);
        $belanjasBySubKegiatan = $this->belanjasBySubKegiatan($user);
        $rekenings = Rekening::where('tipe', 'belanja')->orderBy('kode')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();

        return view('pengeluaran.create', compact(
            'opds', 'programsByOpd', 'kegiatansByProgram',
            'subKegiatansByKegiatan', 'belanjasBySubKegiatan',
            'rekenings', 'sumberDanas'
        ));
    }

    public function edit(Pengeluaran $pengeluaran)
    {
        $this->authorizeOpdRecord($pengeluaran, request()->user());
        $user = request()->user();
        $opds = $this->userOpds($user);

        $programsByOpd = $this->programsByOpd();
        $kegiatansByProgram = $this->kegiatansByProgram($user);
        $subKegiatansByKegiatan = $this->subKegiatansByKegiatan($user);
        $belanjasBySubKegiatan = $this->belanjasBySubKegiatan($user);
        $rekenings = Rekening::where('tipe', 'belanja')->orderBy('kode')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();

        return view('pengeluaran.edit', compact(
            'pengeluaran', 'opds', 'programsByOpd', 'kegiatansByProgram',
            'subKegiatansByKegiatan', 'belanjasBySubKegiatan',
            'rekenings', 'sumberDanas'
        ));
    }

    public function store(StorePengeluaranRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, &$data) {
            $data['tahun_anggaran_id'] = TahunAnggaran::currentActive()?->id;

            if ($data['sumber_dana_id'] ?? null) {
                $sumberDana = SumberDana::find($data['sumber_dana_id']);
                $data['sumber_dana'] = $sumberDana?->nama_sumber_dana;
            }

            if (! $request->user()->isAdmin()) {
                $data['opd_id'] = $request->user()->opd_id;
            }

            Pengeluaran::create($data);
        });

        return back()->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function update(UpdatePengeluaranRequest $request, Pengeluaran $pengeluaran)
    {
        $this->authorizeOpdRecord($pengeluaran, $request->user());

        DB::transaction(function () use ($request, $pengeluaran) {
            $data = $request->validated();

            if ($data['sumber_dana_id'] ?? null) {
                $sumberDana = SumberDana::find($data['sumber_dana_id']);
                $data['sumber_dana'] = $sumberDana?->nama_sumber_dana;
            }

            $pengeluaran->update($data);
        });

        return back()->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        $this->authorizeOpdRecord($pengeluaran, request()->user());
        $pengeluaran->delete();

        return back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
