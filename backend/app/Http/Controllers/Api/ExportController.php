<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Exports\DispensasiExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AuditLog;

class ExportController extends Controller
{
    // Export to Excel
    public function exportExcel(Request $request)
    {
        $user = $request->user();

        // Only admin and kesiswaan can export
        if (!$user->canApproveDispensasi()) {
            return response()->json([
                'message' => 'Unauthorized. Only admin and kesiswaan can export data.',
            ], 403);
        }

        $filters = [
            'status' => $request->get('status', 'all'),
            'kelas_id' => $request->get('kelas_id'),
            'tanggal_mulai' => $request->get('tanggal_mulai'),
            'tanggal_selesai' => $request->get('tanggal_selesai'),
        ];

        //audit log export excel
        AuditLog::log(
            'export_excel',
            "{$user->name} export data dispensasi ke Excel dengan filter: " . json_encode($filters),
            null,
            null,
            null,
            ['filters' => $filters]
        );

        $filename = 'Dispensasi_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new DispensasiExport($filters), $filename);
    }

    // Export to CSV
    public function exportCsv(Request $request)
    {
        $user = $request->user();

        // Only kesiswaan can export
        if (!$user->hasRole('kesiswaan')) {
            return response()->json([
                'message' => 'Unauthorized. Only kesiswaan can export data.',
            ], 403);
        }

        $filters = [
            'status' => $request->get('status', 'all'),
            'kelas_id' => $request->get('kelas_id'),
            'tanggal_mulai' => $request->get('tanggal_mulai'),
            'tanggal_selesai' => $request->get('tanggal_selesai'),
        ];

        //audit log export csv
        AuditLog::log(
            'export_csv',
            "{$user->name} export data dispensasi ke CSV dengan filter: " . json_encode($filters),
            null,
            null,
            null,
            ['filters' => $filters]
        );

        $filename = 'Dispensasi_' . date('Y-m-d_His') . '.csv';

        return Excel::download(new DispensasiExport($filters), $filename, \Maatwebsite\Excel\Excel::CSV);
    }
}
