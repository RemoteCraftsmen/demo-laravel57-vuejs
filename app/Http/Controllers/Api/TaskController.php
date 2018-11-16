<?php

namespace App\Http\Controllers\Api;

use App\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class TaskController
{
    public function destroy(Task $task): JsonResponse
    {
        $loggedUserId = Auth::id();
        $user = $task->user;

        if ($loggedUserId !== $user->id) {
            return response()
                ->json(['status' => 'error', 'error' => 'Forbidden!'])
                ->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        try {
            $task->delete();
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'error' => 'Delete unsuccessful'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['status' => true, 'deleted' => true]);
    }

    public function updateByColumn(int $id): JsonResponse
    {
        $task = Task::find($id);
        $loggedUserId = Auth::id();
        $user = $task->user;

        if ($loggedUserId !== $user->id) {
            return response()
                ->json(['status' => 'error', 'error' => 'Forbidden!'])
                ->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $column = request('column');

        $task->update([
            $column => request($column)
        ]);

        return response()->json(['status' => true, 'updated' => true]);
    }

    public function changeStatusOfTask(int $id): JsonResponse
    {
        $loggedUserId = Auth::id();
        /** @var Task $task */
        $task = Task::find($id);
        $user = $task->user;

        if ($loggedUserId !== $user->id) {
            return response()
                ->json(['status' => 'error', 'error' => 'Forbidden!'])
                ->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $task->update(['completed' => !$task->completed]);

        return response()->json(['status' => true, 'completed' => $task->completed]);
    }
}