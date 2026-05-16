<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\AuditLog;

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            AuditLog::create([
                'user_id' => $user?->id,
                'action' => 'login_failed',
                'description' => "Percobaan login gagal untuk email: {$request->email}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Hapus token lama
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('auth_token')->plainTextToken;

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login_success',
            'description' => "Login berhasil untuk email: {$request->email}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // IMPORTANT: Load relasi kelas dan roles
        $user->load(['kelas', 'roles']);

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'logout',
            'description' => "Logout berhasil untuk email: {$request->user()->email}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    // Get User Profile
    public function profile(Request $request)
    {
        // Load relasi roles
        $user = $request->user()->load(['kelas', 'roles']);

        return response()->json([
            'user' => $user,
        ]);
    }
}
