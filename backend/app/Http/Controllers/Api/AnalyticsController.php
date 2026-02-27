<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    // Get overview statistics
    public function overview(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_dispensasi' => Dispensasi::count(),
            'pending' => Dispensasi::where('status', 'pending')->count(),
            'approved' => Dispensasi::where('status', 'approved')->count(),
            'rejected' => Dispensasi::where('status', 'rejected')->count(),
            'total_siswa' => User::where('role', 'siswa')->count(),
            'total_guru' => User::where('role', 'guru')->count(),
            'dispensasi_bulan_ini' => Dispensasi::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return response()->json($stats);
    }

    // Get dispensasi by month (untuk chart)
    public function dispensasiByMonth(Request $request)
    {
        $year = $request->get('year', now()->year);

        $data = Dispensasi::select(
            DB::raw('MONTH(tanggal) as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
            DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending')
        )
        ->whereYear('tanggal', $year)
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Fill missing months with zeros
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthData = $data->firstWhere('month', $i);
            $months[] = [
                'month' => $i,
                'month_name' => date('F', mktime(0, 0, 0, $i, 1)),
                'total' => $monthData ? $monthData->total : 0,
                'approved' => $monthData ? $monthData->approved : 0,
                'rejected' => $monthData ? $monthData->rejected : 0,
                'pending' => $monthData ? $monthData->pending : 0,
            ];
        }

        return response()->json([
            'year' => $year,
            'data' => $months,
        ]);
    }

    // Get dispensasi by kelas
    public function dispensasiByKelas(Request $request)
    {
        $data = Dispensasi::select(
            'kelas_id',
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
            DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending')
        )
        ->with('kelas:id,nama_kelas')
        ->groupBy('kelas_id')
        ->get()
        ->map(function ($item) {
            return [
                'kelas' => $item->kelas->nama_kelas ?? 'Unknown',
                'total' => $item->total,
                'approved' => $item->approved,
                'rejected' => $item->rejected,
                'pending' => $item->pending,
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    // Get top 10 siswa dengan dispensasi terbanyak
    public function topSiswa(Request $request)
    {
        $limit = $request->get('limit', 10);

        $data = Dispensasi::select('user_id', DB::raw('COUNT(*) as total'))
            ->with('siswa:id,name,nisn', 'siswa.kelas:id,nama_kelas')
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->siswa->name ?? 'Unknown',
                    'nisn' => $item->siswa->nisn ?? '-',
                    'kelas' => $item->siswa->kelas->nama_kelas ?? '-',
                    'total' => $item->total,
                ];
            });

        return response()->json([
            'data' => $data,
        ]);
    }

    // Get dispensasi by mata pelajaran
    public function dispensasiByMapel(Request $request)
    {
        $limit = $request->get('limit', 10);

        $data = Dispensasi::select('mata_pelajaran', DB::raw('COUNT(*) as total'))
            ->groupBy('mata_pelajaran')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $data,
        ]);
    }

    // Get approval rate (persentase approve vs reject)
    public function approvalRate(Request $request)
    {
        $total = Dispensasi::whereIn('status', ['approved', 'rejected'])->count();
        $approved = Dispensasi::where('status', 'approved')->count();
        $rejected = Dispensasi::where('status', 'rejected')->count();

        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 2) : 0;
        $rejectionRate = $total > 0 ? round(($rejected / $total) * 100, 2) : 0;

        return response()->json([
            'total' => $total,
            'approved' => $approved,
            'rejected' => $rejected,
            'approval_rate' => $approvalRate,
            'rejection_rate' => $rejectionRate,
        ]);
    }
}
