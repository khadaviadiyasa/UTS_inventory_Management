<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100'], 'role' => ['nullable', 'in:admin,staff']]);
        $users = User::query()->withCount(['purchases', 'sales'])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($sub) => $sub->where('name', 'like', '%'.$request->string('q').'%')->orWhere('email', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->input('role')))
            ->orderBy('name')->paginate(10)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create(): View { return view('users.form', ['managedUser' => new User()]); }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,staff'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function edit(User $user): View { return view('users.form', ['managedUser' => $user]); }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,staff'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        if ($user->is(auth()->user()) && $data['role'] !== 'admin') {
            return back()->withInput()->withErrors(['role' => 'Role akun yang sedang digunakan tidak dapat diubah dari Admin.']);
        }
        if ($user->role === 'admin' && $data['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withInput()->withErrors(['role' => 'Minimal harus ada satu akun Admin yang aktif.']);
        }
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Minimal harus ada satu akun Admin yang aktif.');
        }
        if ($user->purchases()->exists() || $user->sales()->exists()) {
            return back()->with('error', 'Pengguna memiliki riwayat transaksi. Nonaktifkan aksesnya dengan mengganti password/role sesuai kebijakan, bukan menghapus riwayat.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}