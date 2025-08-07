<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
| These routes are loaded by the RouteServiceProvider and are protected
| by authentication middleware.
|
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    // Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    
    // Users Management
    Route::get('/users', \App\Livewire\Admin\Users\UserIndex::class)->name('users.index')->middleware('can:users.view');
    Route::get('/users/create', \App\Livewire\Admin\Users\UserCreate::class)->name('users.create')->middleware('can:users.create');
    Route::get('/users/{user}', \App\Livewire\Admin\Users\UserShow::class)->name('users.show')->middleware('can:users.view');
    Route::get('/users/{user}/edit', \App\Livewire\Admin\Users\UserEdit::class)->name('users.edit')->middleware('can:users.update');
    
    // Roles & Permissions (will be implemented in later phases)  
    // Route::resource('roles', RoleController::class);
    
    // Activity Logs (will be implemented in later phases)
    // Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    
});