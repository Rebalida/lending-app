<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskResponseController extends Controller
{
    public function show(Request $request, Task $task)
    {
        // Validate token
        abort_if(
            !$request->has('token') || $request->get('token') !== $task->response_token,
            403,
            'Invalid or expired response link.'
        );

        abort_if($task->client_response !== null, 410, 'You have already responded to this task.');

        return view('tasks.respond', compact('task'));
    }

    public function store(Request $request, Task $task)
    {
        abort_if(
            !$request->has('token') || $request->get('token') !== $task->response_token,
            403,
            'Invalid or expired response link.'
        );

        abort_if($task->client_response !== null, 410, 'You have already responded to this task.');

        $validated = $request->validate([
            'response' => 'required|string|max:2000',
        ]);

        $task->recordClientResponse($validated['response']);

        return view('tasks.respond-success', compact('task'));
    }
}
