<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiProjectsController;

Route::prefix('api/projects')->group(function () {
    Route::get('/', [ApiProjectsController::class, 'index']); // List projects
    Route::get('/{id}', [ApiProjectsController::class, 'show']); // View single project
    Route::post('/store', [ApiProjectsController::class, 'store']); // Create project
    Route::put('/update/{id}', [ApiProjectsController::class, 'update']); // Edit project
    Route::delete('/delete/{id}', [ApiProjectsController::class, 'destroy']); // Delete project

    // Contributor management
    Route::post('/{project_id}/contributors/add', [ApiProjectsController::class, 'addContributor']); // Add contributor by email
    Route::get('/{project_id}/contributors', [ApiProjectsController::class, 'listContributors']); // List contributors
    Route::put('/{project_id}/contributors/update/{contributor_id}', [ApiProjectsController::class, 'updateContributor']); // Update contributor permission
    Route::delete('/{project_id}/contributors/delete/{contributor_id}', [ApiProjectsController::class, 'removeContributor']); // Remove contributor
}); 