<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Only admin can view audit logs
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Only admin can view audit logs.',
            ], 403);
        }

        // ✅ SIMPLIFIED QUERY - Tanpa filter dulu untuk test
        $query = AuditLog::with('user');

        // Filter by action
        if ($request->has('action') && $request->action !== '') {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        // Filter by model
        if ($request->has('model') && $request->model !== '') {
            $query->where('model', $request->model);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }

        // ✅ PENTING: Default sorting dan pagination
        $perPage = $request->get('per_page', 20);
        $logs = $query->latest('created_at')->paginate($perPage);

        // ✅ DEBUG: Log ke console
        \Log::info('Audit Logs Query Count: ' . $logs->total());
        \Log::info('Audit Logs Current Page: ' . $logs->currentPage());

        return response()->json($logs);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $log = AuditLog::with('user')->findOrFail($id);

        return response()->json([
            'data' => $log,
        ]);
    }
}
