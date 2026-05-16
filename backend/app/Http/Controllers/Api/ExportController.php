<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\DispensasiExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AuditLog;

class ExportController extends Controller
{
    // Export to Excel
    public function exportExcel(Request $request)
    {
        $user = $request->user();

        if (!$user->canExportDispensasi()) {
            return response()->json([
                'message' => 'Unauthorized. Only admin and kesiswaan can export data.',
            ], 403);
        }

        $filters = $this->filtersFromRequest($request);

        $this->logExport($user, 'export_excel', 'Excel', $filters);

        $filename = 'Dispensasi_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new DispensasiExport($filters), $filename);
    }

    // Export to CSV
    public function exportCsv(Request $request)
    {
        $user = $request->user();

        if (!$user->canExportDispensasi()) {
            return response()->json([
                'message' => 'Unauthorized. Only admin and kesiswaan can export data.',
            ], 403);
        }

        $filters = $this->filtersFromRequest($request);

        $this->logExport($user, 'export_csv', 'CSV', $filters);

        $filename = 'Dispensasi_' . date('Y-m-d_His') . '.csv';

        return Excel::download(
            new DispensasiExport($filters),
            $filename,
            \Maatwebsite\Excel\Excel::CSV,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    private function filtersFromRequest(Request $request): array
    {
        return [
            'status' => $request->get('status', 'all'),
            'kelas_id' => $request->get('kelas_id'),
            'tanggal_mulai' => $request->get('tanggal_mulai'),
            'tanggal_selesai' => $request->get('tanggal_selesai'),
        ];
    }

    private function logExport($user, string $action, string $format, array $filters): void
    {
        AuditLog::log(
            $action,
            "{$user->name} export data dispensasi ke {$format} dengan filter: " . json_encode($filters),
            null,
            null,
            null,
            ['filters' => $filters]
        );
    }
}
