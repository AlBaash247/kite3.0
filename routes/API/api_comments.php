<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiCommentsController;

Route::prefix('comments')->middleware('auth:sanctum')->group(function () {
    Route::get('/ping', [ApiCommentsController::class, 'ping']);
    Route::get('/{task_id}', [ApiCommentsController::class, 'index']); // List comments
    Route::get('/show/{id}', [ApiCommentsController::class, 'show']); // View single comment
    Route::post('/store', [ApiCommentsController::class, 'store']); // Create comment
    Route::put('/update/{id}', [ApiCommentsController::class, 'update']); // Edit comment
    Route::delete('/delete/{id}', [ApiCommentsController::class, 'destroy']); // Delete comment
});
