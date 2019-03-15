<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return ['tasks' => Auth::user()->tasks];
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $task = $user->tasks()->create([
            'name' => $request->name,
            'completed' => false,
        ]);

        return response(compact('task'), RESPONSE::HTTP_CREATED);
    }

    public function destroy(Task $task)
    {
        $user = $task->user;

        $this->authorize('update', $task);

        try {
            $task->delete();
        } catch (\Exception $e) {
            return response(['status' => 'error', 'error' => 'Delete unsuccessful'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return ['status' => true, 'deleted' => true];
    }

    public function patch(Task $task, Request $request)
    {
        $user = $task->user;

        $this->authorize('update', $task);

        $column = $request->column;

        $task->update([
            $column => $request->$column
        ]);

        return ['status' => true, 'updated' => true];
    }

    public function changeStatus(Task $task)
    {
        $user = $task->user;

        $this->authorize('update', $task);

        $task->update(['completed' => !$task->completed]);

        return response()->json(['status' => true, 'completed' => $task->completed]);
    }
}
