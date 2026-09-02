<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermintaanDanaRequest;
use App\Http\Requests\UpdatePermintaanDanaRequest;
use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\PermintaanDana;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\User;
use App\Notifications\PermintaanDanaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermintaanDanaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permintaanDanas = $this->applyOpdScope(PermintaanDana::with(['opd', 'kegiatan', 'subKegiatan', 'belanja', 'sumberDana']), $user)
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
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();

        $kegiatanQuery = Kegiatan::with(['program', 'opd']);
        if (! $user->isAdmin()) {
            $kegiatanQuery->where('opd_id', $user->opd_id);
        }
        $kegiatans = $kegiatanQuery->orderBy('kode_kegiatan')->get();

        $rekenings = Rekening::orderBy('kode')->get();

        return view('permintaan-dana.create', compact('opds', 'sumberDanas', 'kegiatans', 'rekenings'));
    }

    public function store(StorePermintaanDanaRequest $request)
    {
        $data = $request->validated();
        $sumberDana = SumberDana::findOrFail($data['sumber_dana_id']);

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $data['sumber_dana'] = $sumberDana->nama_sumber_dana;
        $data['nomor_permintaan'] = $this->generateNomorPermintaan();
        $data['status'] = 'draft';

        PermintaanDana::create($data);

        return back()->with('success', 'Permintaan dana berhasil dibuat sebagai draft.');
    }

    public function edit(PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());
        $user = request()->user();
        $opds = $this->userOpds($user);
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();

        $kegiatanQuery = Kegiatan::with(['program', 'opd']);
        if (! $user->isAdmin()) {
            $kegiatanQuery->where('opd_id', $user->opd_id);
        }
        $kegiatans = $kegiatanQuery->orderBy('kode_kegiatan')->get();

        $rekenings = Rekening::orderBy('kode')->get();

        return view('permintaan-dana.edit', compact('permintaanDana', 'opds', 'sumberDanas', 'kegiatans', 'rekenings'));
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

        DB::transaction(function () use ($permintaanDana) {
            $permintaanDana = PermintaanDana::findOrFail($permintaanDana->id);

            if ($permintaanDana->status !== 'draft') {
                throw new \RuntimeException('Hanya permintaan draft yang dapat diajukan.');
            }

            $this->commitFunds($permintaanDana);

            $permintaanDana->update([
                'status' => 'menunggu',
                'tanggal' => $permintaanDana->tanggal ?? now(),
            ]);
        });

        $admins = User::where('role', 'admin')->get();
        $nomor = $permintaanDana->fresh()->nomor_permintaan;
        $namaOpd = $permintaanDana->opd->nama ?? 'OPD';

        foreach ($admins as $admin) {
            $admin->notify(new PermintaanDanaNotification(
                $permintaanDana->fresh(),
                'Permintaan Dana Baru',
                "Permintaan dana {$nomor} dari {$namaOpd} menunggu persetujuan.",
                route('persetujuan.index'),
            ));
        }

        return back()->with('success', 'Permintaan dana berhasil diajukan dan menunggu persetujuan.');
    }

    protected function commitFunds(PermintaanDana $permintaanDana): void
    {
        if ($permintaanDana->belanja_id) {
            $belanja = Belanja::find($permintaanDana->belanja_id);
            if ($belanja) {
                $belanja->commit((float) $permintaanDana->jumlah);
            }
        }
    }

    protected function releaseFunds(PermintaanDana $permintaanDana): void
    {
        if ($permintaanDana->belanja_id) {
            $belanja = Belanja::find($permintaanDana->belanja_id);
            if ($belanja) {
                $belanja->releaseCommit((float) $permintaanDana->jumlah);
            }
        }
    }

    protected function generateNomorPermintaan(): string
    {
        $year = now()->format('Y');
        $lastNumber = (int) PermintaanDana::where('nomor_permintaan', 'like', "PD-%/{$year}")
            ->count();

        return 'PD-'.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT).'/'.$year;
    }
}
