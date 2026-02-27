<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\DispensasiController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\AuditLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Test Route
Route::get('/test', function () {
    return response()->json([
        'message' => 'API Laravel berjalan dengan baik!',
        'status' => 'success',
        'timestamp' => now()
    ]);
});

// Public Routes
Route::middleware(['throttle:10,1'])->group(function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['throttle:60,1'])->group(function(){
    Route::get('/kelas', [KelasController::class, 'index']);
    Route::get('/kelas/{id}', [KelasController::class, 'show']);
    Route::get('/roles', function(){
        return response()->json([
            'data' => \App\Models\Role::all()
        ]);
    });
    Route::get('/config/mata-pelajaran', function(){
        return response()->json([
            'data' => config('dispensasi.mata_pelajaran')
        ]);
    });
    Route::get('/config/jam-pelajaran', function(){
        return response()->json([
            'data' => config('dispensasi.jam_pelajaran')
        ]);
    });
});

// Protected Routes (butuh authentication)
Route::middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Dispensasi
    Route::get('/dispensasi', [DispensasiController::class, 'index']);
    Route::post('/dispensasi', [DispensasiController::class, 'store']);
    Route::get('/dispensasi/{id}', [DispensasiController::class, 'show']);
    Route::put('/dispensasi/{id}', [DispensasiController::class, 'update']);
    Route::delete('/dispensasi/{id}', [DispensasiController::class, 'destroy']);

    // Approve/Reject (Guru only)
    Route::put('/dispensasi/{id}/status', [DispensasiController::class, 'updateStatus']);

    // Get mata pelajaran list
    Route::get('/config/mata-pelajaran', function() {
        return response()->json([
            'data' => config('dispensasi.mata_pelajaran')
        ]);
    });

    // Get jam pelajaran list
    Route::get('/config/jam-pelajaran', function() {
        return response()->json([
            'data' => config('dispensasi.jam_pelajaran')
        ]);
    });


    // User Management
    Route::get('/users', [App\Http\Controllers\Api\UserController::class, 'index']);
    Route::get('/users/statistics', [App\Http\Controllers\Api\UserController::class, 'statistics']);
    Route::get('/users/{id}', [App\Http\Controllers\Api\UserController::class, 'show']);
    Route::post('/users', [App\Http\Controllers\Api\UserController::class, 'store']);
    Route::put('/users/{id}', [App\Http\Controllers\Api\UserController::class, 'update']);
    Route::middleware(['throttle:20,1'])->group(function(){
        Route::delete('/users/{id}', [App\Http\Controllers\Api\UserController::class, 'destroy']);
    });

    // Export
    Route::middleware(['throttle:10,1'])->group(function(){
        Route::get('/export/excel', [ExportController::class, 'exportExcel']);
        Route::get('/export/csv', [ExportController::class, 'exportCsv']);
    });

    // Analytics (Admin & Kesiswaan only)
    Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
    Route::get('/analytics/dispensasi-by-month', [AnalyticsController::class, 'dispensasiByMonth']);
    Route::get('/analytics/dispensasi-by-kelas', [AnalyticsController::class, 'dispensasiByKelas']);
    Route::get('/analytics/top-siswa', [AnalyticsController::class, 'topSiswa']);
    Route::get('/analytics/dispensasi-by-mapel', [AnalyticsController::class, 'dispensasiByMapel']);
    Route::get('/analytics/approval-rate', [AnalyticsController::class, 'approvalRate']);

    // Profile Management
    Route::get('/profile/me', [ProfileController::class, 'show']);
    Route::put('/profile/update', [ProfileController::class, 'update']);
    Route::middleware(['throttle:10,1'])->group(function(){
        Route::put('/profile/update-password', [ProfileController::class, 'updatePassword']);
    });

    //Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);

    // Backups (Admin only)
    Route::get('/backups', [BackupController::class, 'index']);
    Route::post('/backups', [BackupController::class, 'create']);
    Route::get('/backups/{filename}/download', [BackupController::class, 'download']);
    Route::delete('/backups/{filename}', [BackupController::class, 'destroy']);
});
