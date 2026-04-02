<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Daftar role yang tersedia
    private $availableRoles = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'kondektur' => 'Kondektur',
        'penumpang' => 'Penumpang',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Filter role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        // Filter status
        if ($request->has('status') && $request->status != '') {
            $status = $request->status == 'active' ? true : false;
            $query->where('is_active', $status);
        }

        $users = $query->latest()->paginate(10);

        return view('superadmin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = $this->availableRoles;
        return view('superadmin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys($this->availableRoles))],
            'phone' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            return redirect()->route('superadmin.users.index')
                ->with('success', 'User berhasil dibuat.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membuat user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('superadmin.users.show', compact('user'));
        } catch (\Exception $e) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'User tidak ditemukan.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            $roles = $this->availableRoles;
            return view('superadmin.users.edit', compact('user', 'roles'));
        } catch (\Exception $e) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'User tidak ditemukan.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'password' => 'nullable|string|min:8|confirmed',
                'role' => ['required', Rule::in(array_keys($this->availableRoles))],
                'phone' => 'nullable|string|max:20',
                'is_active' => 'nullable|boolean',
            ], [
                'name.required' => 'Nama wajib diisi',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah terdaftar',
                'password.min' => 'Password minimal 8 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
                'role.required' => 'Role wajib dipilih',
                'role.in' => 'Role tidak valid',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'phone' => $request->phone,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : $user->is_active,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return redirect()->route('superadmin.users.index')
                ->with('success', 'User berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Cegah penghapusan user sendiri
            $currentUserId = Auth::id();
            if ($user->id === $currentUserId) {
                return redirect()->route('superadmin.users.index')
                    ->with('error', 'Tidak dapat menghapus akun sendiri.');
            }

            // Cegah penghapusan super admin (opsional)
            if ($user->role === 'super_admin') {
                // Hitung total super admin
                $superAdminCount = User::where('role', 'super_admin')->count();
                if ($superAdminCount <= 1) {
                    return redirect()->route('superadmin.users.index')
                        ->with('error', 'Tidak dapat menghapus satu-satunya Super Admin.');
                }
            }

            $user->delete();

            return redirect()->route('superadmin.users.index')
                ->with('success', 'User berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);

            // Cegah nonaktifkan diri sendiri
            $currentUserId = Auth::id();
            if ($user->id === $currentUserId) {
                return redirect()->route('superadmin.users.index')
                    ->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
            }

            $user->update([
                'is_active' => !$user->is_active
            ]);

            $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->route('superadmin.users.index')
                ->with('success', "User berhasil $status.");

        } catch (\Exception $e) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'Gagal mengubah status user: ' . $e->getMessage());
        }
    }

    public function exampleMethod()
    {
        return response()->json(['message' => 'Method example']);
    }
}
