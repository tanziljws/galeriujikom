<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Cek apakah user adalah admin
        if (!auth('petugas')->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat mengakses halaman ini.');
        }
        
        $users = Petugas::orderBy('created_at', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth('petugas')->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
        
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth('petugas')->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
        
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:petugas',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,guest',
        ]);

        Petugas::create($validated);

        return redirect()->route('admin.users.index')
            ->with('status', 'Petugas berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!auth('petugas')->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
        
        $user = Petugas::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!auth('petugas')->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
        
        $user = Petugas::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!auth('petugas')->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
        
        $user = Petugas::findOrFail($id);

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:petugas,username,' . $id,
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|in:admin,guest',
        ]);

        if (!empty($validated['password'])) {
            $user->password = $validated['password']; // akan di-hash otomatis
        }
        
        $user->username = $validated['username'];
        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('status', 'Petugas berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!auth('petugas')->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
        
        $user = Petugas::findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === auth('petugas')->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'Petugas berhasil dihapus!');
    }
}
