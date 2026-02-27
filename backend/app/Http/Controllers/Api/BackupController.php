<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;

class BackupController extends Controller
{
    // List all backups
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access backups.',
            ], 403);
        }

        $disk = Storage::disk('local');

        // Pastikan folder backups exists
        if (!$disk->exists('backups')) {
            $disk->makeDirectory('backups');
        }

        $files = $disk->files('backups');

        $backups = collect($files)->map(function ($file) use ($disk) {
            return [
                'name' => basename($file),
                'path' => $file,
                'size' => $disk->size($file),
                'size_human' => $this->formatBytes($disk->size($file)),
                'created_at' => $disk->lastModified($file),
                'created_at_human' => date('d/m/Y H:i:s', $disk->lastModified($file)),
            ];
        })->sortByDesc('created_at')->values();

        return response()->json([
            'data' => $backups,
            'total' => $backups->count(),
            'total_size' => $this->formatBytes($backups->sum('size')),
        ]);
    }

    // Create new backup - ✅ ENHANCED VERSION
    public function create(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        try {
            Log::info('=== BACKUP START ===');
            Log::info('User: ' . $user->name);
            Log::info('Time: ' . now());

            // Clear previous output
            Artisan::call('config:clear');

            // Run backup command
            Log::info('Running backup:simple command...');
            $exitCode = Artisan::call('backup:simple');

            Log::info('Exit code: ' . $exitCode);

            $output = Artisan::output();
            Log::info('Output: ' . $output);

            // Check exit code
            if ($exitCode !== 0) {
                Log::error('Backup failed with exit code: ' . $exitCode);
                throw new \Exception('Backup command failed with exit code: ' . $exitCode);
            }

            // Check if backup file was created
            $backupFiles = Storage::disk('local')->files('backups');
            $latestBackup = collect($backupFiles)
                ->sortByDesc(fn($file) => Storage::disk('local')->lastModified($file))
                ->first();

            if (!$latestBackup) {
                Log::error('No backup file found after running command');
                throw new \Exception('Backup file was not created');
            }

            Log::info('Latest backup: ' . $latestBackup);
            Log::info('=== BACKUP SUCCESS ===');

            // Log to audit
            AuditLog::log(
                'backup_create',
                "Backup dibuat oleh {$user->name}",
                null,
                null,
                null,
                [
                    'type' => 'manual',
                    'file' => basename($latestBackup),
                    'exit_code' => $exitCode,
                ]
            );

            return response()->json([
                'message' => 'Backup berhasil dibuat',
                'file' => basename($latestBackup),
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            Log::error('=== BACKUP FAILED ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());

            return response()->json([
                'message' => 'Backup gagal: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    // Download backup
    public function download(Request $request, $filename)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $path = 'backups/' . $filename;
        $disk = Storage::disk('local');

        if (!$disk->exists($path)) {
            return response()->json([
                'message' => 'Backup file not found',
            ], 404);
        }

        // Log download
        AuditLog::log(
            'backup_download',
            "Backup {$filename} didownload oleh {$user->name}",
            null,
            null,
            null,
            ['filename' => $filename]
        );

        return Response::download(
            storage_path('app/' . $path),
            $filename,
            [
                'Content-Type' => 'application/zip',
            ]
        );
    }

    // Delete backup
    public function destroy(Request $request, $filename)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $path = 'backups/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return response()->json([
                'message' => 'Backup file not found',
            ], 404);
        }

        Storage::disk('local')->delete($path);

        // Log delete
        AuditLog::log(
            'backup_delete',
            "Backup {$filename} dihapus oleh {$user->name}",
            null,
            null,
            ['filename' => $filename],
            null
        );

        return response()->json([
            'message' => 'Backup berhasil dihapus',
        ]);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
