<?php

use Illuminate\Support\Facades\Route;

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
        'admin.access', // Custom admin access middleware
    ])
    ->group(function () {

        // Dashboard - Available to all authenticated admin users
        Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard.alt');

        // Global Search - Available to all authenticated admin users
        Route::get('/search', \App\Livewire\Admin\SearchResults::class)->name('search');

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
        Route::prefix('settings')->name('settings.')->middleware('roles:Super Admin')->group(function () {
            // Route::get('/', [SettingsController::class, 'index'])->name('index');
            // Route::post('/update', [SettingsController::class, 'update'])->name('update');
        });

        // Activity Logs - Audit trail access
        Route::prefix('activity-logs')->name('activity-logs.')->middleware('can:activity-logs.view')->group(function () {
            Route::get('/', \App\Livewire\Admin\ActivityLogs\ActivityLogIndex::class)->name('index');
        });

        // Blog Management - Permission-based access
        Route::prefix('blog')->name('blog.')->group(function () {

            // Posts Management
            Route::prefix('posts')->name('posts.')->group(function () {
                Route::get('/', \App\Livewire\Admin\Blog\Posts\PostIndex::class)
                    ->name('index')
                    ->middleware('can:posts.view');

                Route::get('/create', \App\Livewire\Admin\Blog\Posts\PostForm::class)
                    ->name('create')
                    ->middleware('can:posts.create');

                Route::get('/{post}/edit', \App\Livewire\Admin\Blog\Posts\PostForm::class)
                    ->name('edit')
                    ->middleware('can:posts.update')
                    ->where('post', '[0-9]+');

                Route::get('/{post}', \App\Livewire\Admin\Blog\Posts\PostShow::class)
                    ->name('show')
                    ->middleware('can:posts.view')
                    ->where('post', '[0-9]+');
                // Blog Show Route (for slug-based URLs)
                Route::get('/{slug}', \App\Livewire\Admin\Blog\Posts\PostShow::class)
                    ->name('show')
                    ->middleware('can:posts.view')
                    ->where('slug', '[a-z0-9\-]+');
            });

            // Categories Management
            Route::prefix('categories')->name('categories.')->group(function () {
                Route::get('/', \App\Livewire\Admin\Blog\Categories\CategoryIndex::class)
                    ->name('index');
                // ->middleware('can:categories.view');

                Route::get('/create', \App\Livewire\Admin\Blog\Categories\CategoryForm::class)
                    ->name('create')
                    ->middleware('can:categories.create');

                Route::get('/{category}/edit', \App\Livewire\Admin\Blog\Categories\CategoryForm::class)
                    ->name('edit')
                    ->middleware('can:categories.update')
                    ->where('category', '[0-9]+');

                Route::get('/{category}', \App\Livewire\Admin\Blog\Categories\CategoryShow::class)
                    ->name('show')
                    ->middleware('can:categories.view')
                    ->where('category', '[0-9]+');
            });

            // Tags Management
            Route::prefix('tags')->name('tags.')->group(function () {
                Route::get('/', \App\Livewire\Admin\Blog\Tags\TagIndex::class)
                    ->name('index')
                    ->middleware('can:tags.view');

                Route::get('/create', \App\Livewire\Admin\Blog\Tags\TagForm::class)
                    ->name('create')
                    ->middleware('can:tags.create');

                Route::get('/{tag}/edit', \App\Livewire\Admin\Blog\Tags\TagForm::class)
                    ->name('edit')
                    ->middleware('can:tags.update')
                    ->where('tag', '[0-9]+');

                Route::get('/{tag}', \App\Livewire\Admin\Blog\Tags\TagShow::class)
                    ->name('show')
                    ->middleware('can:tags.view')
                    ->where('tag', '[0-9]+');
            });
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
