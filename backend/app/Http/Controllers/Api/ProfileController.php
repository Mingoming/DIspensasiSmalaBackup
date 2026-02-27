<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\AuditLog;

class ProfileController extends Controller
{
    // Get current user profile
    public function show(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['kelas', 'roles']),
        ]);
    }

    // Update profile
    public function update(Request $request)
    {
        $user = $request->user();

        //old value
        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'no_telepon' => $user->no_telepon,
            'nisn' => $user->nisn,
            'nip' => $user->nip,
            'mata_pelajaran' => $user->mata_pelajaran,
            'kelas_id' => $user->kelas_id,
        ];

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'no_telepon' => 'nullable|string',
            'nisn' => 'nullable|string',
            'nip' => 'nullable|string',
            'mata_pelajaran' => 'nullable|string',
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'no_telepon' => $request->no_telepon,
            'nisn' => $request->nisn,
            'nip' => $request->nip,
            'mata_pelajaran' => $request->mata_pelajaran,
            'kelas_id' => $request->kelas_id,
        ]);

        $user->load(['kelas', 'roles']);

        //new value
        $newValues = [
            'name' => $user->name,
            'email' => $user->email,
            'no_telepon' => $user->no_telepon,
            'nisn' => $user->nisn,
            'nip' => $user->nip,
            'mata_pelajaran' => $user->mata_pelajaran,
            'kelas_id' => $user->kelas_id,
        ];

        //audit log
        $changes = [];
        foreach ($newValues as $key => $value){
            if($oldValues[$key] != $value){
                $changes[] = "{$key}: '{$oldValues[$key]}' => '{$value}'";
            }
        }
        if(count($changes) > 0){
            $changesDescription = implode(', ', $changes);

            AuditLog::log(
                'update',
                "{$user->name} memperbarui profil. Perubahan: {$changesDescription}",
                'User',
                $user->id,
                $oldValues,
                $newValues
            );
        }

        return response()->json([
            'message' => 'Profile berhasil diperbarui',
            'user' => $user,
        ]);
    }

    // Update password
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            //audit log failed attempt
            AuditLog::log(
                'update_password_failed',
                "{$user->name} mencoba mengubah password tetapi password lama salah",
                'User',
                $user->id,
                null,
                null
            );

            return response()->json([
                'message' => 'Password saat ini tidak sesuai',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        //audit log success
        AuditLog::log(
            'update_password',
            "{$user->name} berhasil mengubah password",
            'User',
            $user->id,
            null,
            null
        );

        return response()->json([
            'message' => 'Password berhasil diubah',
        ]);
    }
}
