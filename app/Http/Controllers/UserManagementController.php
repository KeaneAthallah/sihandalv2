<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('opd')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalOpd = User::where('role', 'opd')->whereNotNull('opd_id')->count();

        return view('user-management.index', compact(
            'users', 'totalUsers', 'totalAdmins', 'totalOpd'
        ));
    }

    public function create(): View
    {
        $opds = Opd::orderBy('nama')->get();

        return view('user-management.create', compact('opds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,opd'],
            'opd_id' => ['nullable', 'required_if:role,opd', 'exists:opds,id'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'opd_id' => $request->role === 'opd' ? $request->opd_id : null,
        ]);

        return redirect()->route('user-management.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $opds = Opd::orderBy('nama')->get();

        return view('user-management.edit', compact('user', 'opds'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,opd'],
            'opd_id' => ['nullable', 'required_if:role,opd', 'exists:opds,id'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'opd_id' => $request->role === 'opd' ? $request->opd_id : null,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('user-management.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun sendiri.']);
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
