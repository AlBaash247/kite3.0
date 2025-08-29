<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Include API route files
require __DIR__.'/API/api_auth.php';
require __DIR__.'/API/api_projects.php';
require __DIR__.'/API/api_comments.php';
require __DIR__.'/API/api_tasks.php';
require __DIR__.'/API/api_task_assignments.php';
