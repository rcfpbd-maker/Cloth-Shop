<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Services\BackupService;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    protected $backupService;
    protected $activityLogService;
    protected $notificationService;

    public function __construct(
        BackupService $backupService,
        ActivityLogService $activityLogService,
        NotificationService $notificationService
    ) {
        $this->backupService = $backupService;
        $this->activityLogService = $activityLogService;
        $this->notificationService = $notificationService;
    }

    /**
     * List all backup logs
     */
    public function index()
    {
        $backups = BackupLog::with('creator')->latest()->paginate(20);
        return response()->json($backups);
    }

    /**
     * Manually trigger a backup
     */
    public function store(Request $request)
    {
        $backup = $this->backupService->runBackup();

        if ($backup) {
            // Log activity
            $this->activityLogService->log('accounts', 'create', "Manual database backup created: {$backup->file_name}", $backup->id);

            // Notify admins
            $this->notificationService->send([
                'module' => 'accounts',
                'type' => 'backup_complete',
                'message' => "Database backup completed successfully: {$backup->file_name}",
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Backup created successfully',
                'backup' => $backup
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create backup'
        ], 500);
    }

    /**
     * Download a specific backup file
     */
    public function download($id)
    {
        $backup = BackupLog::findOrFail($id);
        $path = storage_path('app/backups/' . $backup->file_name);

        if (!file_exists($path)) {
            return response()->json(['message' => 'File not found on server'], 404);
        }

        return response()->download($path);
    }

    /**
     * Restore from a specific backup
     */
    public function restore($id)
    {
        $backup = BackupLog::findOrFail($id);

        try {
            $this->backupService->runRestore($backup->file_name);

            // Log activity
            $this->activityLogService->log('accounts', 'update', "Database restored from backup: {$backup->file_name}", $backup->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Database restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Restoration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a backup file and its log
     */
    public function destroy($id)
    {
        $backup = BackupLog::findOrFail($id);
        $path = storage_path('app/backups/' . $backup->file_name);

        if (file_exists($path)) {
            unlink($path);
        }

        $backup->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Backup deleted successfully'
        ]);
    }
}
