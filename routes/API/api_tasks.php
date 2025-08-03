<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiTasksController;

Route::prefix('tasks')->middleware('auth:sanctum')->group(function () {
    Route::get('/ping', [ApiTasksController::class, 'ping']);
    Route::get('/', [ApiTasksController::class, 'index']); // List tasks
    Route::get('show/{id}', [ApiTasksController::class, 'show']); // View single task
    Route::post('/store', [ApiTasksController::class, 'store']); // Create task
    Route::put('/update/{id}', [ApiTasksController::class, 'update']); // Edit task
    Route::delete('/delete/{id}', [ApiTasksController::class, 'destroy']); // Delete task
}); 