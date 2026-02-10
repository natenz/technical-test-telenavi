<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;

class ChartController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');

        if (!$type) {
            return response()->json(['error' => 'Parameter type is required'], 400);
        }

        switch ($type) {
            case 'status':
                return $this->statusSummary();
            case 'priority':
                return $this->prioritySummary();
            case 'assignee':
                return $this->assigneeSummary();
            default:
                return response()->json(['error' => 'Invalid type parameter'], 400);
        }
    }
    private function statusSummary()
    {
        $summary = Todo::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Pastikan semua status ada walau count=0
        $allStatus = ['pending', 'open', 'in_progress', 'completed'];
        $result = [];
        foreach ($allStatus as $status) {
            $result[$status] = $summary->get($status, 0);
        }

        return response()->json(['status_summary' => $result]);
    }
    private function prioritySummary()
    {
        $summary = Todo::selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        // Pastikan semua priority ada walau count=0
        $allPriority = ['low', 'medium', 'high'];
        $result = [];
        foreach ($allPriority as $priority) {
            $result[$priority] = $summary->get($priority, 0);
        }

        return response()->json(['priority_summary' => $result]);
    }
    private function assigneeSummary()
    {
        $todos = Todo::select('assignee', 'status', 'time_tracked')->get();

        $result = [];

        foreach ($todos as $todo) {
            $assignee = $todo->assignee ?? 'Unassigned';

            if (!isset($result[$assignee])) {
                $result[$assignee] = [
                    'total_todos' => 0,
                    'total_pending_todos' => 0,
                    'total_timetracked_completed_todos' => 0.0
                ];
            }

            $result[$assignee]['total_todos'] += 1;

            if ($todo->status === 'pending') {
                $result[$assignee]['total_pending_todos'] += 1;
            }

            if ($todo->status === 'completed') {
                $result[$assignee]['total_timetracked_completed_todos'] += (float) ($todo->time_tracked ?? 0);
            }
        }

        return response()->json(['assignee_summary' => $result]);
    }
}
