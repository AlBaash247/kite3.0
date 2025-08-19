<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiProjectsController;

Route::prefix('projects')->middleware('auth:sanctum')->group(function () {
    Route::get('/ping', [ApiProjectsController::class, 'ping']);
    Route::get('/', [ApiProjectsController::class, 'index']); // List projects
    Route::get('/show/{id}', [ApiProjectsController::class, 'show']); // View single project
    Route::post('/store', [ApiProjectsController::class, 'store']); // Create project
    Route::post('/update', [ApiProjectsController::class, 'update']); // Edit project
    Route::delete('/delete/{id}', [ApiProjectsController::class, 'destroy']); // Delete project

    // Contributor management
    Route::post('/contributors/add', [ApiProjectsController::class, 'addContributor']); // Add contributor by email
    Route::get('/contributors/{project_id}', [ApiProjectsController::class, 'listContributors']); // List contributors
    Route::post('/contributors/update/', [ApiProjectsController::class, 'updateContributor']); // Update contributor permission
    Route::post('/contributors/delete', [ApiProjectsController::class, 'removeContributor']); // Remove contributor
    Route::delete('/contributors/delete/{id}', [ApiProjectsController::class, 'removeContributorById']); // Remove contributor
});
