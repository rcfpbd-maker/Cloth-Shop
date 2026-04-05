<?php

namespace App\Services;

use App\Models\BackupLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupService
{
    /**
     * Run database backup
     */
    public function runBackup()
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $fileName = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $fileName);

        // Ensure directory exists
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        // We use mysqldump. NOTE: This requires mysqldump to be in the system path.
        // For Windows, we might need the full path to mysqldump.exe if not in PATH.
        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > \"{$path}\"";
        
        // Use shell_exec for direct execution on Windows
        shell_exec($command);

        if (file_exists($path)) {
            $size = filesize($path);
            $sizeFormatted = $this->formatBytes($size);

            return BackupLog::create([
                'file_name' => $fileName,
                'file_size' => $sizeFormatted,
                'backup_date' => now(),
                'created_by' => auth()->id() ?? 1,
            ]);
        }

        return null;
    }

    /**
     * Restore database from backup
     */
    public function runRestore($fileName)
    {
        $path = storage_path('app/backups/' . $fileName);
        if (!file_exists($path)) {
            throw new \Exception("Backup file not found.");
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = "mysql --user={$username} --password={$password} --host={$host} {$database} < \"{$path}\"";
        
        shell_exec($command);

        return true;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
