<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get(),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'data' => Kelas::with('siswa')->findOrFail($id),
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:100|unique:kelas,nama_kelas',
            'tingkat' => 'required|string|max:20',
            'jurusan' => 'nullable|string|max:100',
        ]);

        $kelas = Kelas::create($validated);

        return response()->json([
            'message' => 'Kelas berhasil ditambahkan',
            'data' => $kelas,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $kelas = Kelas::findOrFail($id);

        $validated = $request->validate([
            'nama_kelas' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kelas', 'nama_kelas')->ignore($kelas->id),
            ],
            'tingkat' => 'required|string|max:20',
            'jurusan' => 'nullable|string|max:100',
        ]);

        $kelas->update($validated);

        return response()->json([
            'message' => 'Kelas berhasil diperbarui',
            'data' => $kelas,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $kelas = Kelas::withCount(['siswa', 'jadwalMengajar'])->findOrFail($id);

        if ($kelas->siswa_count > 0) {
            return response()->json([
                'message' => 'Kelas tidak bisa dihapus karena masih memiliki siswa.',
            ], 422);
        }

        if ($kelas->jadwal_mengajar_count > 0) {
            return response()->json([
                'message' => 'Kelas tidak bisa dihapus karena masih memiliki jadwal mengajar.',
            ], 422);
        }

        $kelas->delete();

        return response()->json([
            'message' => 'Kelas berhasil dihapus',
        ]);
    }
}
