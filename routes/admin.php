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
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Users Management (will be implemented in later phases)
    // Route::resource('users', UserController::class);
    
    // Roles & Permissions (will be implemented in later phases)  
    // Route::resource('roles', RoleController::class);
    
    // Activity Logs (will be implemented in later phases)
    // Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    
});