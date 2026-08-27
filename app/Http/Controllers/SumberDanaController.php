<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSumberDanaRequest;
use App\Http\Requests\UpdateSumberDanaRequest;
use App\Models\SumberDana;
use Illuminate\Http\Request;

class SumberDanaController extends Controller
{
    public function index(Request $request)
    {
        $sumberDanas = SumberDana::latest()->paginate(20);

        return view('sumber-dana.index', ['sumberDanas' => $sumberDanas]);
    }

    public function create()
    {
        return view('sumber-dana.create');
    }

    public function edit(SumberDana $sumberDana)
    {
        return view('sumber-dana.edit', compact('sumberDana'));
    }

    public function store(StoreSumberDanaRequest $request)
    {
        SumberDana::create($request->validated());

        return back()->with('success', 'Sumber dana berhasil ditambahkan.');
    }

    public function update(UpdateSumberDanaRequest $request, SumberDana $sumberDana)
    {
        $sumberDana->update($request->validated());

        return back()->with('success', 'Sumber dana berhasil diperbarui.');
    }

    public function destroy(SumberDana $sumberDana)
    {
        $sumberDana->delete();

        return back()->with('success', 'Sumber dana berhasil dihapus.');
    }
}
