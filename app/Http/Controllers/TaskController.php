<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class TaskController extends Controller
{
    public function index()
    {
        $tasks = Auth::user()->tasks;

        return view('tasks.list', compact('tasks'));
    }
}
