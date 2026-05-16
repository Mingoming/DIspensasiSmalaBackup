<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\JadwalMengajar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;

class DispensasiController extends Controller
{
    // Get All Dispensasi
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Dispensasi::with($this->dispensasiRelations())->latest();

        if ($user->canViewAllDispensasi()) {
            $data = $query->get();
        } elseif ($user->isGuru()) {
            $data = $this->getGuruDispensasi($query, $user);
        } else {
            $query->where('user_id', $user->id);
            $data = $query->get();
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    // Create Dispensasi (Siswa)
    public function store(Request $request)
    {
        if (!$request->user()->isSiswa()) {
            return response()->json([
                'message' => 'Unauthorized. Hanya siswa yang bisa membuat dispensasi.',
            ], 403);
        }

        $request->merge([
            'mata_pelajaran' => $this->normalizeMataPelajaranInput($request->input('mata_pelajaran')),
        ]);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'jam_pelajaran_mulai' => 'required|integer|min:1|max:8',
            'jam_pelajaran_selesai' => 'required|integer|min:1|max:8|gte:jam_pelajaran_mulai',
            'mata_pelajaran' => 'required|array|min:1',
            'mata_pelajaran.*' => 'required|string|max:100',
            'keperluan' => 'required|string',
            'surat_dispensasi' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        $data['mata_pelajaran'] = implode(', ', array_unique($data['mata_pelajaran']));

        $data['user_id'] = $request->user()->id;
        $data['kelas_id'] = $request->user()->kelas_id;
        $data['status'] = 'pending';

        // Upload file jika ada
        if ($request->hasFile('surat_dispensasi')) {
            $file = $request->file('surat_dispensasi');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('dispensasi', $filename, 'public');
            $data['surat_dispensasi'] = $path;
        }

        $dispensasi = Dispensasi::create($data);

        AuditLog::log(
            'create',
            "Siswa {$request->user()->name} mengajukan dispensasi untuk tanggal {$data['tanggal']} mata pelajaran {$data['mata_pelajaran']}",
            'Dispensasi',
            $dispensasi->id,
            null,
            $dispensasi->toArray()
        );

        return response()->json([
            'message' => 'Dispensasi berhasil diajukan',
            'data' => $dispensasi->load(['siswa', 'kelas']),
        ], 201);
    }

    // Show Single Dispensasi
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $dispensasi = Dispensasi::with($this->dispensasiRelations())->findOrFail($id);

        if (!$this->canViewDispensasi($user, $dispensasi)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($user->isGuru()) {
            $this->attachGuruScheduleContext($dispensasi, $user);
        }

        return response()->json([
            'data' => $dispensasi,
        ]);
    }


    // Update Dispensasi (Siswa - hanya jika status pending)
    public function update(Request $request, $id)
    {
        $dispensasi = Dispensasi::findOrFail($id);
        $oldValues = $dispensasi->toArray();

        // Cek apakah siswa pemilik dispensasi
        if ($dispensasi->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Cek apakah masih pending
        if ($dispensasi->status !== 'pending') {
            return response()->json([
                'message' => 'Dispensasi sudah diproses, tidak bisa diubah',
            ], 400);
        }

        $request->merge([
            'mata_pelajaran' => $this->normalizeMataPelajaranInput($request->input('mata_pelajaran')),
        ]);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'jam_pelajaran_mulai' => 'required|integer|min:1|max:8',
            'jam_pelajaran_selesai' => 'required|integer|min:1|max:8|gte:jam_pelajaran_mulai',
            'mata_pelajaran' => 'required|array|min:1',
            'mata_pelajaran.*' => 'required|string|max:100',
            'keperluan' => 'required|string',
            'surat_dispensasi' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        $data['mata_pelajaran'] = implode(', ', array_unique($data['mata_pelajaran']));

        // Upload file baru jika ada
        if ($request->hasFile('surat_dispensasi')) {
            // Hapus file lama
            if ($dispensasi->surat_dispensasi) {
                Storage::disk('public')->delete($dispensasi->surat_dispensasi);
            }

            $file = $request->file('surat_dispensasi');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('dispensasi', $filename, 'public');
            $data['surat_dispensasi'] = $path;
        }

        $dispensasi->update($data);

        AuditLog::log(
            'update',
            "Siswa {$request->user()->name} mengubah dispensasi #{$id} untuk tanggal {$data['tanggal']} mata pelajaran {$data['mata_pelajaran']}",
            'Dispensasi',
            $dispensasi->id,
            $oldValues,
            $dispensasi->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Dispensasi berhasil diperbarui',
            'data' => $dispensasi->load(['siswa', 'kelas']),
        ]);
    }

    // Delete Dispensasi (Siswa - hanya jika status pending)
    public function destroy(Request $request, $id)
    {
        $dispensasi = Dispensasi::findOrFail($id);
        $dispensasiData = $dispensasi->toArray();

        // Cek apakah siswa pemilik dispensasi
        if ($dispensasi->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Cek apakah masih pending
        if ($dispensasi->status !== 'pending') {
            return response()->json([
                'message' => 'Dispensasi sudah diproses, tidak bisa dihapus',
            ], 400);
        }

        // Hapus file jika ada
        if ($dispensasi->surat_dispensasi) {
            Storage::disk('public')->delete($dispensasi->surat_dispensasi);
        }

        $dispensasi->delete();

        AuditLog::log(
            'delete',
            "Dispensasi #{$id} dihapus oleh {$request->user()->name}",
            'Dispensasi',
            $id,
            $dispensasiData,
            null
        );

        return response()->json([
            'message' => 'Dispensasi berhasil dihapus',
        ]);
    }

    // Approve/Reject Dispensasi (Kesiswaan)
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->canApproveDispensasi()) {
            return response()->json([
                'message' => 'Unauthorized. Hanya Kesiswaan yang bisa approve.',
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'catatan' => 'nullable|string',
        ]);

        $dispensasi = Dispensasi::findOrFail($id);
        $oldStatus = $dispensasi->status;

        $dispensasi->update([
            'status' => $request->status,
            'approved_by' => $user->id,
            'catatan' => $request->catatan,
        ]);

        //log approval/rejection
        $action = $request->status === 'approved' ? 'approve' : 'reject';
        AuditLog::log(
            $action,
            "Dispensasi #{$id} di{$action} oleh {$user->name}. Status: {$oldStatus} -> {$request->status}",
            'Dispensasi',
            $dispensasi->id,
            ['status' => $oldStatus],
            ['status' => $request->status, 'catatan' => $request->catatan]
        );

        return response()->json([
            'message' => 'Status dispensasi berhasil diperbarui',
            'data' => $dispensasi->load(['siswa', 'kelas', 'approver']),
        ]);
    }

    private function normalizeMataPelajaranInput($value): array
    {
        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value)) {
            $items = explode(',', $value);
        } else {
            $items = [];
        }

        return array_values(array_filter(array_map('trim', $items)));
    }

    private function dispensasiRelations(): array
    {
        return [
            'siswa',
            'kelas',
            'approver',
        ];
    }

    private function canViewDispensasi(User $user, Dispensasi $dispensasi): bool
    {
        if ($user->canViewAllDispensasi() || $dispensasi->user_id === $user->id) {
            return true;
        }

        if (!$user->isGuru()) {
            return false;
        }

        return $this->matchingSchedulesForDispensasi($dispensasi, $this->guruSchedules($user))->isNotEmpty();
    }

    private function getGuruDispensasi($query, User $user)
    {
        $schedules = $this->guruSchedules($user);

        if ($schedules->isEmpty()) {
            return collect();
        }

        $query->whereIn('kelas_id', $schedules->pluck('kelas_id')->unique()->values());

        return $query->get()
            ->filter(fn (Dispensasi $dispensasi) => $this->matchingSchedulesForDispensasi($dispensasi, $schedules)->isNotEmpty())
            ->values()
            ->each(fn (Dispensasi $dispensasi) => $this->attachGuruScheduleContext($dispensasi, $user, $schedules));
    }

    private function attachGuruScheduleContext(Dispensasi $dispensasi, User $user, ?EloquentCollection $schedules = null): void
    {
        $dispensasi->setRelation(
            'jadwalMengajarGuru',
            $this->matchingSchedulesForDispensasi($dispensasi, $schedules ?? $this->guruSchedules($user))->values()
        );
    }

    private function matchingSchedulesForDispensasi(Dispensasi $dispensasi, EloquentCollection $schedules)
    {
        return $schedules->filter(
            fn (JadwalMengajar $jadwal) => $this->dispensasiMatchesSchedule($dispensasi, $jadwal)
        );
    }

    private function dispensasiMatchesSchedule(Dispensasi $dispensasi, JadwalMengajar $jadwal): bool
    {
        return $dispensasi->kelas_id === $jadwal->kelas_id
            && $this->hariFromDate($dispensasi->tanggal) === $jadwal->hari
            && $dispensasi->jam_pelajaran_mulai <= $jadwal->jam_pelajaran_selesai
            && $dispensasi->jam_pelajaran_selesai >= $jadwal->jam_pelajaran_mulai
            && in_array(
                strtolower($jadwal->mataPelajaran?->nama ?? ''),
                $this->mataPelajaranNames($dispensasi),
                true
            );
    }

    private function guruSchedules(User $user): EloquentCollection
    {
        return $user->jadwalMengajar()
            ->with(['kelas', 'mataPelajaran'])
            ->get();
    }

    private function mataPelajaranNames(Dispensasi $dispensasi): array
    {
        return collect(explode(',', $dispensasi->mata_pelajaran))
            ->map(fn (string $mapel) => strtolower(trim($mapel)))
            ->filter()
            ->values()
            ->all();
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
