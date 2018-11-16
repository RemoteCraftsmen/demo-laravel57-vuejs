<?php

namespace App\Http\Controllers;

use App\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class TaskController extends Controller
{
    public function index()
    {
        $loggedUserId = Auth::id();
        $tasks = Task::where('user_id', $loggedUserId)->get();

        return view('tasks.list', compact('tasks'));
    }

    public function store(Request $request)
    {
        $loggedUserId = Auth::id();
        $user = Auth::user();

        $task = new Task([
            'name' => '',
            'description' => '',
            'completed' => false,
        ]);
        $user->tasks()->save($task);

        $tasks = Task::where('user_id', $loggedUserId)->get();

        return redirect('/tasks')->with(['tasks' => $tasks]);
    }
}
