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
| by authentication middleware with rate limiting and permission checks.
|
*/

// Admin route group with comprehensive middleware
Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth', 
        'verified', 
        'throttle:admin', // Custom admin rate limiting
        'admin.access'    // Custom admin access middleware
    ])
    ->group(function () {
        
        // Dashboard - Available to all authenticated admin users
        Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard.alt');
        
        // Users Management - Permission-based access
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Users\UserIndex::class)
                ->name('index')
                ->middleware('can:users.view');
                
            Route::get('/create', \App\Livewire\Admin\Users\UserCreate::class)
                ->name('create')
                ->middleware('can:users.create');
                
            Route::get('/{user}', \App\Livewire\Admin\Users\UserShow::class)
                ->name('show')
                ->middleware('can:users.view')
                ->where('user', '[0-9]+'); // Route constraint for numeric IDs
                
            Route::get('/{user}/edit', \App\Livewire\Admin\Users\UserEdit::class)
                ->name('edit')
                ->middleware('can:users.update')
                ->where('user', '[0-9]+');
        });
        
        // Roles & Permissions Management - High-level permissions required
        Route::prefix('roles')->name('roles.')->middleware('can:roles.view')->group(function () {
            Route::get('/', \App\Livewire\Admin\Roles\RoleIndex::class)->name('index');
        });
        
        // System Settings - Super admin only
        Route::prefix('settings')->name('settings.')->middleware('role:Super Admin')->group(function () {
            // Route::get('/', [SettingsController::class, 'index'])->name('index');
            // Route::post('/update', [SettingsController::class, 'update'])->name('update');
        });
        
        // Activity Logs - Audit trail access
        Route::prefix('activity-logs')->name('activity-logs.')->middleware('can:activity-logs.view')->group(function () {
            // Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            // Route::get('/{activity}', [ActivityLogController::class, 'show'])->name('show');
        });
        
        // API Management - For future API endpoint management
        Route::prefix('api-management')->name('api.')->middleware('role:Super Admin|Admin')->group(function () {
            // Route::get('/tokens', [ApiTokenController::class, 'index'])->name('tokens.index');
            // Route::post('/tokens', [ApiTokenController::class, 'store'])->name('tokens.store');
        });
        
        // System Health & Monitoring - Admin and above
        Route::prefix('system')->name('system.')->middleware('role:Super Admin|Admin')->group(function () {
            // Route::get('/health', [SystemController::class, 'health'])->name('health');
            // Route::get('/logs', [SystemController::class, 'logs'])->name('logs');
        });
    });
