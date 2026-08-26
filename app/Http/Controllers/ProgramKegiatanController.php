<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramKegiatanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $programs = $this->applyOpdScope(Program::with('opd'), $user)
            ->orderBy('kode_kegiatan')
            ->get();

        $uniqueKegiatans = $programs->unique('kode_kegiatan');
        $totalPagu = $programs->sum('pagu');

        return view('program-kegiatan.index', compact('programs', 'uniqueKegiatans', 'totalPagu'));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());

        return view('program-kegiatan.create', compact('opds'));
    }

    public function edit(Program $program)
    {
        $this->authorizeOpdRecord($program, request()->user());
        $opds = $this->userOpds(request()->user());

        return view('program-kegiatan.edit', compact('program', 'opds'));
    }

    public function store(StoreProgramRequest $request)
    {
        $data = $request->validated();
        $data['persentase'] = $data['pagu'] > 0 ? round(($data['realisasi'] ?? 0) / $data['pagu'] * 100, 2) : 0;

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        Program::create($data);

        return back()->with('success', 'Program berhasil ditambahkan.');
    }

    public function update(UpdateProgramRequest $request, Program $program)
    {
        $this->authorizeOpdRecord($program, $request->user());
        $data = $request->validated();
        $data['persentase'] = $data['pagu'] > 0 ? round(($data['realisasi'] ?? 0) / $data['pagu'] * 100, 2) : 0;

        $program->update($data);

        return back()->with('success', 'Program berhasil diperbarui.');
    }

    public function destroy(Program $program)
    {
        $this->authorizeOpdRecord($program, request()->user());
        $program->delete();

        return back()->with('success', 'Program berhasil dihapus.');
    }
}
