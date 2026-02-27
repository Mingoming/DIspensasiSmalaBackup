<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\AuditLog;

class UserController extends Controller
{
    // Get all users (Admin only)
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this resource.',
            ], 403);
        }

        $query = User::with(['kelas', 'roles']);

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $users = $query->latest()->paginate($perPage);

        return response()->json($users);
    }

    // Get single user
    public function show(Request $request, $id)
    {
        $authUser = $request->user();

        if (!$authUser->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $user = User::with(['kelas', 'roles', 'dispensasi'])->findOrFail($id);

        return response()->json([
            'data' => $user,
        ]);
    }

    // Create new user (Admin only)
    public function store(Request $request)
    {
        $authUser = $request->user();

        if (!$authUser->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:siswa,guru,admin',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'nisn' => 'required_if:role,siswa|nullable|string',
            'nip' => 'required_if:role,guru,admin|nullable|string',
            'mata_pelajaran' => 'nullable|string',
            'kelas_id' => 'required_if:role,siswa|nullable|exists:kelas,id',
            'no_telepon' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'nisn' => $request->nisn,
            'nip' => $request->nip,
            'mata_pelajaran' => $request->mata_pelajaran,
            'kelas_id' => $request->kelas_id,
            'no_telepon' => $request->no_telepon,
        ]);

        // Attach roles if guru/admin
        if ($request->role !== 'siswa' && $request->has('roles') && !empty($request->roles)) {
            $roleIds = Role::whereIn('name', $request->roles)->pluck('id');
            $user->roles()->attach($roleIds);
        }

        $user->load(['kelas', 'roles']);

        AuditLog::log(
            'create',
            "User baru '{$user->name}' ({$user->email}) dibuat oleh {$authUser->name} dengan role {$user->role}",
            'User',
            $user->id,
            null,
            [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'nisn' => $user->nisn,
                'nip' => $user->nip,
                'kelas_id' => $user->kelas_id,
                ]
        );

        return response()->json([
            'message' => 'User berhasil dibuat',
            'data' => $user,
        ], 201);
    }

    // Update user
    public function update(Request $request, $id)
    {
        $authUser = $request->user();

        if (!$authUser->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $user = User::findOrFail($id);

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'nisn' => $user->nisn,
            'nip' => $user->nip,
            'mata_pelajaran' => $user->mata_pelajaran,
            'kelas_id' => $user->kelas_id,
            'no_telepon' => $user->no_telepon,
        ];

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:siswa,guru,admin',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'nisn' => 'required_if:role,siswa|nullable|string',
            'nip' => 'required_if:role,guru,admin|nullable|string',
            'mata_pelajaran' => 'nullable|string',
            'kelas_id' => 'required_if:role,siswa|nullable|exists:kelas,id',
            'no_telepon' => 'nullable|string',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'nisn' => $request->nisn,
            'nip' => $request->nip,
            'mata_pelajaran' => $request->mata_pelajaran,
            'kelas_id' => $request->kelas_id,
            'no_telepon' => $request->no_telepon,
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update roles
        if ($request->role !== 'siswa') {
            $user->roles()->detach();
            if ($request->has('roles') && !empty($request->roles)) {
                $roleIds = Role::whereIn('name', $request->roles)->pluck('id');
                $user->roles()->attach($roleIds);
            }
        }

        $user->load(['kelas', 'roles']);

        $newValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'nisn' => $user->nisn,
            'nip' => $user->nip,
            'mata_pelajaran' => $user->mata_pelajaran,
            'kelas_id' => $user->kelas_id,
            'no_telepon' => $user->no_telepon,
        ];

        //audit log
        $changes = [];
        foreach ($newValues as $key => $value) {
            if ($oldValues[$key] != $value){
                $changes[] = "{$key}: '{$oldValues[$key]}' => '{$value}'";
            }
        }
        $changesDescription = implode(', ', $changes);

        AuditLog::log(
            'update',
            "User '{$user->name}' diperbarui oleh {$authUser->name}. Perubahan: {$changesDescription}",
            $user->id,
            $oldValues,
            $newValues
        );

        return response()->json([
            'message' => 'User berhasil diperbarui',
            'data' => $user,
        ]);
    }

    // Delete user (Soft delete)
    public function destroy(Request $request, $id)
    {
        $authUser = $request->user();

        if (!$authUser->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $user = User::findOrFail($id);

        // Prevent deleting self
        if ($user->id === $authUser->id) {
            return response()->json([
                'message' => 'Tidak dapat menghapus akun sendiri.',
            ], 400);
        }

        //simpan data untuk log
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'nisn' => $user->nisn,
            'nip' => $user->nip,
        ];

        $user->delete();

        //audit log
        AuditLog::log(
            'delete',
            "User '{$userData['name']}' ({$userData['email']}) dengan ID #{$userData['id']} dihapus oleh {$authUser->name}",
            'User',
            $id,
            $userData,
            null
        );

        return response()->json([
            'message' => 'User berhasil dihapus',
        ]);
    }

    // Get statistics
    public function statistics(Request $request)
    {
        $authUser = $request->user();

        if (!$authUser->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $stats = [
            'total_users' => User::count(),
            'total_siswa' => User::where('role', 'siswa')->count(),
            'total_guru' => User::where('role', 'guru')->count(),
            'total_admin' => User::where('role', 'admin')->count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json($stats);
    }
}
