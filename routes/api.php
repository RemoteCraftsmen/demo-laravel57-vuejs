<?php

Route::middleware(['auth'])->group(function () {
    Route::patch('/tasks/{task}/complete', 'Api\TaskController@changeStatus');
    Route::patch('/tasks/{task}', 'Api\TaskController@patch');
    Route::delete('/tasks/{task}', 'Api\TaskController@destroy');
    Route::get('/tasks', 'Api\TaskController@index');
    Route::post('/tasks', 'Api\TaskController@store');
});
