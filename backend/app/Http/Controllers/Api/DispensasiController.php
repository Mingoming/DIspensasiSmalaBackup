<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;

class DispensasiController extends Controller
{
    // Get All Dispensasi
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Dispensasi::with(['siswa', 'kelas', 'approver'])->latest();

        if (!$user->canViewAllDispensasi()) {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'data' => $query->get(),
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

        $dispensasi = Dispensasi::with(['siswa', 'kelas', 'approver'])->findOrFail($id);

        if (!$user->canViewAllDispensasi() && $dispensasi->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
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
}
