<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\WebTasksController;
use App\Http\Controllers\Web\WebProjectsController;
use App\Http\Controllers\Web\WebCommentsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



Route::prefix('projects')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [WebProjectsController::class, 'index'])->name('projects.index'); // List projects
    Route::get('/create', [WebProjectsController::class, 'create'])->name('projects.create'); // Show create form
    Route::get('/{id}', [WebProjectsController::class, 'show'])->name('projects.show'); // View single project
    Route::get('/{id}/edit', [WebProjectsController::class, 'edit'])->name('projects.edit'); // Show edit form
    Route::post('/store', [WebProjectsController::class, 'store'])->name('projects.store'); // Create project
    Route::put('/update/{id}', [WebProjectsController::class, 'update'])->name('projects.update'); // Edit project
    Route::delete('/delete/{id}', [WebProjectsController::class, 'destroy'])->name('projects.destroy'); // Delete project

    // Contributor management
    Route::get('/{project_id}/contributors', [WebProjectsController::class, 'contributors'])->name('projects.contributors'); // Show contributors page
    Route::post('/{project_id}/contributors/add', [WebProjectsController::class, 'addContributor'])->name('projects.contributors.add'); // Add contributor by email
    Route::put('/{project_id}/contributors/update/{contributor_id}', [WebProjectsController::class, 'updateContributor'])->name('projects.contributors.update'); // Update contributor permission
    Route::delete('/{project_id}/contributors/delete/{contributor_id}', [WebProjectsController::class, 'removeContributor'])->name('projects.contributors.remove'); // Remove contributor
}); 

Route::prefix('tasks')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [WebTasksController::class, 'index'])->name('tasks.index'); // List tasks
    Route::get('/create', [WebTasksController::class, 'create'])->name('tasks.create'); // Show create form
    Route::get('/{id}', [WebTasksController::class, 'show'])->name('tasks.show'); // View single task
    Route::get('/{id}/edit', [WebTasksController::class, 'edit'])->name('tasks.edit'); // Show edit form
    Route::post('/store', [WebTasksController::class, 'store'])->name('tasks.store'); // Create task
    Route::put('/update/{id}', [WebTasksController::class, 'update'])->name('tasks.update'); // Edit task
    Route::delete('/delete/{id}', [WebTasksController::class, 'destroy'])->name('tasks.destroy'); // Delete task
}); 

Route::prefix('comments')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [WebCommentsController::class, 'index'])->name('comments.index'); // List comments
    Route::get('/create', [WebCommentsController::class, 'create'])->name('comments.create'); // Show create form
    Route::get('/{id}', [WebCommentsController::class, 'show'])->name('comments.show'); // View single comment
    Route::get('/{id}/edit', [WebCommentsController::class, 'edit'])->name('comments.edit'); // Show edit form
    Route::post('/store', [WebCommentsController::class, 'store'])->name('comments.store'); // Create comment
    Route::put('/update/{id}', [WebCommentsController::class, 'update'])->name('comments.update'); // Edit comment
    Route::delete('/delete/{id}', [WebCommentsController::class, 'destroy'])->name('comments.destroy'); // Delete comment
}); 


require __DIR__.'/auth.php';