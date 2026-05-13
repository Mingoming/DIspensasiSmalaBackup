<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MataPelajaranController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => MataPelajaran::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:mata_pelajaran,nama',
            'aktif' => 'nullable|boolean',
        ]);

        $mapel = MataPelajaran::create([
            'nama' => $validated['nama'],
            'aktif' => $validated['aktif'] ?? true,
        ]);

        return response()->json([
            'message' => 'Mata pelajaran berhasil ditambahkan',
            'data' => $mapel,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $mapel = MataPelajaran::findOrFail($id);

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('mata_pelajaran', 'nama')->ignore($mapel->id),
            ],
            'aktif' => 'required|boolean',
        ]);

        $mapel->update($validated);

        return response()->json([
            'message' => 'Mata pelajaran berhasil diperbarui',
            'data' => $mapel,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        MataPelajaran::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Mata pelajaran berhasil dihapus',
        ]);
    }
}
