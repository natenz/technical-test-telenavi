<?php

namespace App\Services;

use App\Models\Todo;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TodoExportService
{
    public function exportExcel(array $filters = [])
    {
        // Build query
        $query = Todo::query();

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['assignee'])) {
            $assignees = array_map('trim', explode(',', $filters['assignee']));
            $query->whereIn('assignee', $assignees);
        }

        if (!empty($filters['start']) && !empty($filters['end'])) {
            $query->whereBetween('due_date', [$filters['start'], $filters['end']]);
        }

        if (!empty($filters['min']) && !empty($filters['max'])) {
            $query->whereBetween('time_tracked', [$filters['min'], $filters['max']]);
        }

        if (!empty($filters['status'])) {
            $statuses = array_map('trim', explode(',', $filters['status']));
            $query->whereIn('status', $statuses);
        }

        if (!empty($filters['priority'])) {
            $priorities = array_map('trim', explode(',', $filters['priority']));
            $query->whereIn('priority', $priorities);
        }

        $todos = $query->get();

        // Buat spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->fromArray(['Title', 'Assignee', 'Due Date', 'Time Tracked', 'Status', 'Priority'], NULL, 'A1');

        // Data
        $sheet->fromArray(
            $todos->map(fn($t) => [
                $t->title,
                $t->assignee,
                $t->due_date,
                $t->time_tracked,
                $t->status,
                $t->priority
            ])->toArray(),
            NULL,
            'A2'
        );

        // Summary row
        $summaryRow = $todos->count() + 2;
        $sheet->setCellValue('A' . $summaryRow, 'Total Todos: ' . $todos->count());
        $sheet->setCellValue('D' . $summaryRow, 'Total Time Tracked: ' . (float) $todos->sum('time_tracked'));

        // Pastikan folder ada
        $dir = storage_path('app/public/file');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Buat nama file unik per request
        $filePath = $dir . '/todo-report-' . time() . '.xlsx';

        // Simpan file fisik
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file untuk download langsung
        return response()->download($filePath, 'todo-report.xlsx');
    }
}
