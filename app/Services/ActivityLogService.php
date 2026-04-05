<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log a user activity
     *
     * @param string $module
     * @param string $actionType (create, update, delete, return, adjustment, etc.)
     * @param string $description
     * @param int|null $referenceId
     * @return void
     */
    public function log($module, $actionType, $description, $referenceId = null)
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'module' => $module,
                'action_type' => $actionType,
                'action' => $actionType, // For backward compatibility with existing 'action' column
                'description' => $description,
                'reference_id' => $referenceId,
                'ip_address' => Request::ip(),
            ]);
        } catch (\Exception $e) {
            // Log the error but don't break the main process
            \Log::error("Failed to create activity log: " . $e->getMessage());
        }
    }
}
