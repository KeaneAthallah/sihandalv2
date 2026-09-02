<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUptRequest;
use App\Http\Requests\UpdateUptRequest;
use App\Models\Upt;
use Illuminate\Http\Request;

class UptController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $upts = $this->applyOpdScope(Upt::with('opd'), $user)
            ->orderBy('nama')
            ->get();

        $opds = $this->userOpds($user);

        return view('upt.index', compact('upts', 'opds'));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());

        return view('upt.create', compact('opds'));
    }

    public function store(StoreUptRequest $request)
    {
        $data = $request->validated();

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        Upt::create($data);

        return back()->with('success', 'UPT berhasil ditambahkan.');
    }

    public function edit(Upt $upt)
    {
        $this->authorizeOpdRecord($upt, request()->user());
        $opds = $this->userOpds(request()->user());

        return view('upt.edit', compact('upt', 'opds'));
    }

    public function update(UpdateUptRequest $request, Upt $upt)
    {
        $this->authorizeOpdRecord($upt, $request->user());
        $upt->update($request->validated());

        return back()->with('success', 'UPT berhasil diperbarui.');
    }

    public function destroy(Upt $upt)
    {
        $this->authorizeOpdRecord($upt, request()->user());
        $upt->delete();

        return back()->with('success', 'UPT berhasil dihapus.');
    }
}
