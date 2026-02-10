<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    //
    public function store(StoreTodoRequest $request)
{
    $todo = Todo::create([
        'title' => $request->title,
        'assignee' => $request->assignee,
        'due_date' => $request->due_date,
        'time_tracked' => $request->time_tracked ?? 0,
        'status' => $request->status ?? 'pending',
        'priority' => $request->priority,
    ]);

    return response()->json([
        'message' => 'Todo created successfully',
        'data' => $todo
    ], 201);
}
}
