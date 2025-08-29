<?php

use App\Http\Controllers\Api\TaskAssignmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Task assignment routes
    Route::prefix('tasks/{task}/assignments')->group(function () {
        Route::get('/ping', [TaskAssignmentController::class, 'ping']);

        Route::post('/assign', [TaskAssignmentController::class, 'assign']);
        Route::post('/unassign', [TaskAssignmentController::class, 'unassign']);
        Route::get('/show', [TaskAssignmentController::class, 'taskAssignments']);
    });

});

