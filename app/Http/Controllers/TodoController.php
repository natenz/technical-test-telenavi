<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;

class TodoController extends Controller
{
    // GET /api/todos
public function index(Request $request)
{
    $perPage = $request->get('per_page', 10);

    $todos = Todo::orderBy('created_at', 'desc')
        ->paginate($perPage);

    return response()->json([
        'data' => $todos->items(),
        'pagination' => [
            'current_page' => $todos->currentPage(),
            'last_page' => $todos->lastPage(),
            'per_page' => $todos->perPage(),
            'total' => $todos->total(),
        ]
    ]);
}

    // POST /api/todos
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'assignee' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'time_tracked' => 'nullable|numeric',
            'status' => 'nullable|in:pending,open,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
        ]);

        $todo = Todo::create($validated);

        return response()->json([
            'message' => 'Todo created successfully',
            'data' => $todo
        ], 201);
    }
}

