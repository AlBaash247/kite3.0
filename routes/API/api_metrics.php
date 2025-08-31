<?php

use App\Http\Controllers\Api\ApiMetricsController;
use Illuminate\Support\Facades\Route;

//TODO: Add metrics controller
Route::prefix('metrics')->middleware('auth:sanctum')->group(function () {

    Route::get('/ping', [ApiMetricsController::class, 'ping']);
    Route::get('/', [ApiMetricsController::class, 'index']);
    Route::get('/due-today', [ApiMetricsController::class, 'taskDueTodayList']);
    Route::get('/due-soon', [ApiMetricsController::class, 'taskDueIn7DaysList']);
    Route::get('/due-passed', [ApiMetricsController::class, 'taskPastDueList']);
});
