<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalMengajar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JadwalMengajarController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'data' => JadwalMengajar::with(['guru', 'kelas', 'mataPelajaran'])
                ->orderBy('hari')
                ->orderBy('jam_pelajaran_mulai')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $this->validateSchedule($request);
        $conflict = $this->findConflict($validated);

        if ($conflict) {
            return response()->json(['message' => $conflict], 422);
        }

        $jadwal = JadwalMengajar::create($validated)->load(['guru', 'kelas', 'mataPelajaran']);

        return response()->json([
            'message' => 'Jadwal mengajar berhasil ditambahkan',
            'data' => $jadwal,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'data' => JadwalMengajar::with(['guru', 'kelas', 'mataPelajaran'])->findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $jadwal = JadwalMengajar::findOrFail($id);
        $validated = $this->validateSchedule($request);
        $conflict = $this->findConflict($validated, $jadwal->id);

        if ($conflict) {
            return response()->json(['message' => $conflict], 422);
        }

        $jadwal->update($validated);

        return response()->json([
            'message' => 'Jadwal mengajar berhasil diperbarui',
            'data' => $jadwal->load(['guru', 'kelas', 'mataPelajaran']),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        JadwalMengajar::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Jadwal mengajar berhasil dihapus',
        ]);
    }

    private function validateSchedule(Request $request): array
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'hari' => ['required', Rule::in(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'])],
            'jam_pelajaran_mulai' => 'required|integer|min:1|max:8',
            'jam_pelajaran_selesai' => 'required|integer|min:1|max:8|gte:jam_pelajaran_mulai',
        ]);

        $guru = User::with('roles')->findOrFail($validated['user_id']);

        if ($guru->role !== 'guru' && !$guru->hasRole('guru_mapel')) {
            throw ValidationException::withMessages([
                'user_id' => 'Jadwal hanya bisa diberikan kepada guru.',
            ]);
        }

        return $validated;
    }

    private function findConflict(array $data, ?int $ignoreId = null): ?string
    {
        $overlap = function ($query) use ($data) {
            $query->where('jam_pelajaran_mulai', '<=', $data['jam_pelajaran_selesai'])
                ->where('jam_pelajaran_selesai', '>=', $data['jam_pelajaran_mulai']);
        };

        $guruConflict = JadwalMengajar::where('hari', $data['hari'])
            ->where('user_id', $data['user_id'])
            ->where($overlap)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($guruConflict) {
            return 'Guru sudah memiliki jadwal pada rentang jam tersebut.';
        }

        $kelasConflict = JadwalMengajar::where('hari', $data['hari'])
            ->where('kelas_id', $data['kelas_id'])
            ->where($overlap)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        return $kelasConflict ? 'Kelas sudah memiliki jadwal pada rentang jam tersebut.' : null;
    }
}
