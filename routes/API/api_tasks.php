<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiTasksController;
use App\Http\Controllers\Api\TaskAssignmentController;

Route::prefix('tasks')->middleware('auth:sanctum')->group(function () {




    Route::get('/ping', [ApiTasksController::class, 'ping']);
    Route::get('/show/{task}', [ApiTasksController::class, 'show']); // View single task
    Route::post('/store', [ApiTasksController::class, 'store']); // Create task
    Route::post('/update', [ApiTasksController::class, 'update']); // Edit task
    Route::delete('/delete/{task}', [ApiTasksController::class, 'destroy']); // Delete task

    // User's assigned tasks
    Route::get('/my-assignments', [TaskAssignmentController::class, 'myAssignments']);

    //keep this last to avoid conflict with other routes
    Route::get('/{project}', [ApiTasksController::class, 'index']); // List tasks







});
