<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKegiatanRequest;
use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateKegiatanRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class ProgramKegiatanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $kegiatanQuery = Kegiatan::with(['program', 'opd', 'subKegiatans.belanjas.rekening'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id))
            ->latest('kegiatan.id');

        $kegiatans = $kegiatanQuery->get();
        $programs = Program::withCount(['kegiatans'])
            ->with(['opd'])
            ->whereIn('id', $kegiatans->pluck('program_id')->unique())
            ->orderBy('kode_program')
            ->get();
        $grouped = $kegiatans->groupBy('program_id');
        $totalPagu = $kegiatans->sum('pagu');

        $opds = $this->userOpds($user);

        return view('program-kegiatan.index', compact('kegiatans', 'programs', 'grouped', 'totalPagu', 'opds'));
    }

    public function create()
    {
        $user = request()->user();
        $opds = $this->userOpds($user);
        $tahunAnggarans = TahunAnggaran::orderByDesc('tahun')->get();

        return view('program-kegiatan.create', compact('opds', 'tahunAnggarans'));
    }

    public function edit(Program $program)
    {
        $user = request()->user();
        $this->authorizeProgram($program, $user);
        $opds = $this->userOpds($user);
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();
        $rekenings = Rekening::orderBy('kode')->get();
        $tahunAnggarans = TahunAnggaran::orderByDesc('tahun')->get();

        $kegiatans = $program->kegiatans()->with(['opd', 'sumberDana', 'subKegiatans.belanjas.rekening'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id))
            ->orderBy('kode_kegiatan')
            ->get();

        return view('program-kegiatan.edit', compact('program', 'kegiatans', 'opds', 'sumberDanas', 'rekenings', 'tahunAnggarans'));
    }

    public function store(StoreProgramRequest $request)
    {
        $data = $request->validated();

        if (! $request->user()->isAdmin() && empty($data['opd_id'])) {
            $data['opd_id'] = $request->user()->opd_id;
        } elseif (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        Program::create($data);

        return Redirect::route('program-kegiatan.index')->with('success', 'Program berhasil ditambahkan.');
    }

    public function update(UpdateProgramRequest $request, Program $program)
    {
        $this->authorizeProgram($program, $request->user());
        $program->update($request->validated());

        return back()->with('success', 'Program berhasil diperbarui.');
    }

    public function destroy(Program $program)
    {
        $this->authorizeProgram($program, request()->user());
        $program->delete();

        return Redirect::route('program-kegiatan.index')->with('success', 'Program berhasil dihapus.');
    }

    public function storeKegiatan(StoreKegiatanRequest $request, Program $program)
    {
        $this->authorizeProgram($program, $request->user());
        $data = $this->kegiatanData($request);
        $data['program_id'] = $program->id;

        DB::transaction(function () use ($data) {
            $kegiatan = Kegiatan::create($data);

            if (($data['kode_sub_kegiatan'] ?? null) && ($data['nama_sub_kegiatan'] ?? null)) {
                SubKegiatan::create([
                    'kegiatan_id' => $kegiatan->id,
                    'kode_sub_kegiatan' => $data['kode_sub_kegiatan'],
                    'nama_sub_kegiatan' => $data['nama_sub_kegiatan'],
                    'pagu' => 0,
                    'realisasi' => 0,
                    'persentase' => 0,
                ]);
            }
        });

        return back()->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function updateKegiatan(UpdateKegiatanRequest $request, Program $program, Kegiatan $kegiatan)
    {
        $this->authorizeProgram($program, $request->user());
        $this->authorizeOpdRecord($kegiatan, $request->user());
        $kegiatan->update($this->kegiatanData($request));

        return back()->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroyKegiatan(Program $program, Kegiatan $kegiatan)
    {
        $this->authorizeProgram($program, request()->user());
        $this->authorizeOpdRecord($kegiatan, request()->user());
        $kegiatan->delete();

        return back()->with('success', 'Kegiatan berhasil dihapus.');
    }

    protected function kegiatanData(Request $request): array
    {
        $data = $request->validated();
        $realisasi = (float) ($data['realisasi'] ?? 0);

        $data['realisasi'] = $realisasi;
        $data['persentase'] = $data['pagu'] > 0 ? round($realisasi / $data['pagu'] * 100, 2) : 0;

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        return $data;
    }

    protected function authorizeProgram(Program $program, $user): void
    {
        if ($user === null || $user->isAdmin()) {
            return;
        }

        $belongsToOtherOpd = $program->kegiatans()->where('opd_id', '!=', $user->opd_id)->exists();

        if ($belongsToOtherOpd) {
            abort(403);
        }

        if ($program->opd_id !== null && (int) $program->opd_id !== (int) $user->opd_id) {
            abort(403);
        }
    }
}
