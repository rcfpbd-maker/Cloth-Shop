<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs (Admin/Manager only)
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Filters
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('module')) {
            $query->where('module', $request->module);
        }
        if ($request->has('action_type')) {
            $query->where('action_type', $request->action_type);
        }
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(50);
        return response()->json($logs);
    }
}
