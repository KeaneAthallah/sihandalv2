<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesBudgetHierarchyCascade;
use App\Http\Requests\StorePermintaanDanaRequest;
use App\Http\Requests\UpdatePermintaanDanaRequest;
use App\Models\Belanja;
use App\Models\PermintaanDana;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Services\PermintaanDanaService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PermintaanDanaController extends Controller
{
    use ProvidesBudgetHierarchyCascade;

    public function __construct(private readonly PermintaanDanaService $workflow) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $permintaanQuery = $this->applyOpdScope(PermintaanDana::with(['opd', 'kegiatan', 'subKegiatan', 'belanja', 'sumberDana']), $user)
            ->orderBy('created_at', 'desc');

        $totalPermintaan = (clone $permintaanQuery)->sum('jumlah');
        $totalDisetujui = (clone $permintaanQuery)->where('status', 'disetujui')->sum('jumlah');
        $totalMenunggu = (clone $permintaanQuery)->where('status', 'menunggu')->sum('jumlah');
        $totalPermintaanCount = (clone $permintaanQuery)->count();
        $totalMenungguCount = (clone $permintaanQuery)->where('status', 'menunggu')->count();

        $permintaanDanas = $permintaanQuery->paginate(15);

        $opds = $this->userOpds($user);

        return view('permintaan-dana.index', compact(
            'permintaanDanas', 'totalPermintaan', 'totalDisetujui',
            'totalMenunggu', 'totalPermintaanCount', 'totalMenungguCount', 'opds'
        ));
    }

    public function create(): View
    {
        $user = request()->user();
        $opds = $this->userOpds($user);
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();
        $programsByOpd = $this->programsByOpd();
        $kegiatansByProgram = $this->kegiatansByProgram($user);
        $subKegiatansByKegiatan = $this->subKegiatansByKegiatan($user);
        $belanjasBySubKegiatan = $this->belanjasBySubKegiatan($user);

        return view('permintaan-dana.create', compact(
            'opds', 'sumberDanas', 'programsByOpd', 'kegiatansByProgram',
            'subKegiatansByKegiatan', 'belanjasBySubKegiatan'
        ));
    }

    public function store(StorePermintaanDanaRequest $request)
    {
        $data = $request->validated();
        $sumberDana = SumberDana::findOrFail($data['sumber_dana_id']);

        // Rekening selalu mengikuti belanja yang dipilih agar
        // pengeluaran nanti konsisten dengan sumber anggaran.
        $data['rekening_id'] = Belanja::findOrFail($data['belanja_id'])->rekening_id;

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $data['sumber_dana'] = $sumberDana->nama_sumber_dana;
        $data['nomor_permintaan'] = $this->workflow->nextNomorPermintaan();
        $data['status'] = 'draft';
        $data['tahun_anggaran_id'] = TahunAnggaran::currentActive()?->id;

        PermintaanDana::create($data);

        return back()->with('success', 'Permintaan dana berhasil dibuat sebagai draft.');
    }

    public function edit(PermintaanDana $permintaanDana): View
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());
        $user = request()->user();
        $opds = $this->userOpds($user);
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();
        $programsByOpd = $this->programsByOpd();
        $kegiatansByProgram = $this->kegiatansByProgram($user);
        $subKegiatansByKegiatan = $this->subKegiatansByKegiatan($user);
        $belanjasBySubKegiatan = $this->belanjasBySubKegiatan($user);

        return view('permintaan-dana.edit', compact(
            'permintaanDana', 'opds', 'sumberDanas', 'programsByOpd', 'kegiatansByProgram',
            'subKegiatansByKegiatan', 'belanjasBySubKegiatan'
        ));
    }

    public function update(UpdatePermintaanDanaRequest $request, PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, $request->user());

        if (! in_array($permintaanDana->status, ['draft', 'ditolak'])) {
            return back()->withErrors(['status' => 'Hanya permintaan draft atau ditolak yang dapat diedit.']);
        }

        $data = $request->validated();
        $data['sumber_dana'] = SumberDana::findOrFail($data['sumber_dana_id'])->nama_sumber_dana;
        $data['rekening_id'] = Belanja::findOrFail($data['belanja_id'])->rekening_id;

        $permintaanDana->update($data);

        return back()->with('success', 'Permintaan dana berhasil diperbarui.');
    }

    public function submit(Request $request, PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, $request->user());

        try {
            $this->workflow->submit($permintaanDana, $request->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['jumlah' => $e->getMessage()]);
        }

        return back()->with('success', 'Permintaan dana berhasil diajukan dan menunggu persetujuan.');
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
}
