<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Routes specific to tenant domains
|
*/

Route::middleware(['web', 'tenant'])->group(function () {
    Route::get('/', function () {
        $tenant = app('tenant');

        return view('tenant.dashboard', compact('tenant'));
    })->name('tenant.dashboard');

    // File management routes
    Route::middleware(['auth'])->prefix('files')->group(function () {
        Route::get('/', [App\Http\Controllers\Tenant\FileController::class, 'index'])->name('files.index');
        Route::get('/create', [App\Http\Controllers\Tenant\FileController::class, 'create'])->name('files.create');
        Route::post('/', [App\Http\Controllers\Tenant\FileController::class, 'store'])->name('files.store');
        Route::get('/{file}', [App\Http\Controllers\Tenant\FileController::class, 'show'])->name('files.show');
        Route::get('/{file}/edit', [App\Http\Controllers\Tenant\FileController::class, 'edit'])->name('files.edit');
        Route::put('/{file}', [App\Http\Controllers\Tenant\FileController::class, 'update'])->name('files.update');
        Route::delete('/{file}', [App\Http\Controllers\Tenant\FileController::class, 'destroy'])->name('files.destroy');
    });

    // Folder management routes
    Route::middleware(['auth'])->prefix('folders')->group(function () {
        Route::get('/', [App\Http\Controllers\Tenant\FolderController::class, 'index'])->name('folders.index');
        Route::get('/create', [App\Http\Controllers\Tenant\FolderController::class, 'create'])->name('folders.create');
        Route::post('/', [App\Http\Controllers\Tenant\FolderController::class, 'store'])->name('folders.store');
        Route::get('/{folder}', [App\Http\Controllers\Tenant\FolderController::class, 'show'])->name('folders.show');
        Route::get('/{folder}/edit', [App\Http\Controllers\Tenant\FolderController::class, 'edit'])->name('folders.edit');
        Route::put('/{folder}', [App\Http\Controllers\Tenant\FolderController::class, 'update'])->name('folders.update');
        Route::delete('/{folder}', [App\Http\Controllers\Tenant\FolderController::class, 'destroy'])->name('folders.destroy');
    });

    // User management routes (tenant specific)
    Route::middleware(['auth', 'role:admin'])->prefix('users')->group(function () {
        Route::get('/', [App\Http\Controllers\Tenant\UserController::class, 'index'])->name('users.index');
        Route::get('/create', [App\Http\Controllers\Tenant\UserController::class, 'create'])->name('users.create');
        Route::post('/', [App\Http\Controllers\Tenant\UserController::class, 'store'])->name('users.store');
        Route::get('/{user}', [App\Http\Controllers\Tenant\UserController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [App\Http\Controllers\Tenant\UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [App\Http\Controllers\Tenant\UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [App\Http\Controllers\Tenant\UserController::class, 'destroy'])->name('users.destroy');
    });
});
