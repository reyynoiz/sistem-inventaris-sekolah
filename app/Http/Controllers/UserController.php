<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::withCount('loans')
            ->when($request->q, fn ($query, $search) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            ))
            ->when($request->role, fn ($query, $role) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create', ['user' => new User]);
    }

    public function store(UserRequest $request)
    {
        $user = User::create($request->validated()); // password otomatis di-hash oleh cast 'hashed'
        ActivityLog::record('create_user', "Menambah akun {$user->name} ({$user->role})");

        return redirect()->route('users.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();

        // Admin tidak boleh menurunkan role akunnya sendiri (nanti kehilangan akses)
        if ($user->is($request->user()) && $data['role'] !== 'admin') {
            return back()->withInput()->with('error', 'Anda tidak bisa mengubah role akun yang sedang dipakai.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        ActivityLog::record('update_user', "Mengubah akun {$user->name} ({$user->role})");

        return redirect()->route('users.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa menghapus akun yang sedang dipakai.');
        }

        // withTrashed: riwayat peminjaman yang sudah di-soft-delete tetap menahan penghapusan
        if ($user->loans()->withTrashed()->exists()) {
            return back()->with('error', 'Akun ini punya riwayat peminjaman, jadi tidak bisa dihapus.');
        }

        $name = $user->name;
        $user->delete();
        ActivityLog::record('delete_user', "Menghapus akun {$name}");

        return redirect()->route('users.index')->with('success', 'Akun berhasil dihapus.');
    }
}