<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::patch('/tasks/update/column/{id}', 'Api\TaskController@updateByColumn');
    Route::patch('/tasks/complete/{id}', 'Api\TaskController@changeStatusOfTask');
    Route::delete('/tasks/{task}', 'Api\TaskController@destroy');
});
