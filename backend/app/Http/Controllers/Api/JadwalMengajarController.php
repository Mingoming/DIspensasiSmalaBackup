<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalMengajar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JadwalMengajarController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isGuru()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = JadwalMengajar::with(['guru', 'kelas', 'mataPelajaran'])
            ->orderBy('hari')
            ->orderBy('jam_pelajaran_mulai');

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'data' => $query->get(),
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
        $jadwal = JadwalMengajar::with(['guru', 'kelas', 'mataPelajaran'])->findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && (!$user->isGuru() || $jadwal->user_id !== $user->id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'data' => $jadwal,
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

    public function affectedSchedules(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam_pelajaran_mulai' => 'required|integer|min:1|max:8',
            'jam_pelajaran_selesai' => 'required|integer|min:1|max:8|gte:jam_pelajaran_mulai',
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        $user = $request->user();
        $kelasId = $user->isSiswa()
            ? $user->kelas_id
            : ($validated['kelas_id'] ?? null);

        if (!$kelasId) {
            return response()->json([
                'message' => 'Kelas tidak ditemukan untuk menghitung jadwal terdampak.',
            ], 422);
        }

        $hari = $this->hariFromDate($validated['tanggal']);

        $jadwal = JadwalMengajar::with(['guru', 'kelas', 'mataPelajaran'])
            ->where('kelas_id', $kelasId)
            ->where('hari', $hari)
            ->where('jam_pelajaran_mulai', '<=', $validated['jam_pelajaran_selesai'])
            ->where('jam_pelajaran_selesai', '>=', $validated['jam_pelajaran_mulai'])
            ->orderBy('jam_pelajaran_mulai')
            ->get();

        return response()->json([
            'data' => $jadwal,
            'mata_pelajaran' => $jadwal
                ->pluck('mataPelajaran.nama')
                ->filter()
                ->unique()
                ->values(),
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

        if (!$guru->isGuru()) {
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

    private function hariFromDate($date): string
    {
        return [
            0 => 'minggu',
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ][Carbon::parse($date)->dayOfWeek];
    }
}
