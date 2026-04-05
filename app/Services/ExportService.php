<?php

namespace App\Services;

use App\Models\ExportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportService
{
    /**
     * Export data to CSV (Excel compatible)
     */
    public function exportToCsv($data, $filename, $type, $filters = [])
    {
        $handle = fopen('php://temp', 'w+');
        
        // Add UTF-8 BOM for Excel
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        if (!empty($data)) {
            // Header
            fputcsv($handle, array_keys((array)$data[0]));

            // Data rows
            foreach ($data as $row) {
                fputcsv($handle, (array)$row);
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $this->logExport($type, 'excel', $filters);

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    /**
     * Export data to PDF
     */
    public function exportToPdf($view, $data, $filename, $type, $filters = [])
    {
        $pdf = Pdf::loadView($view, $data);
        
        $this->logExport($type, 'pdf', $filters);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Log the export action
     */
    protected function logExport($type, $format, $filters)
    {
        ExportLog::create([
            'user_id' => auth()->id() ?? 1, // Fallback to ID 1 for system/dev
            'export_type' => $type,
            'format' => $format,
            'filters' => $filters,
        ]);
    }
}
