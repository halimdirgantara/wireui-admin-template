<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureRouteModelBinding();
        $this->loadRoutes();
    }
    
    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Admin panel rate limiting - more restrictive
        RateLimiter::for('admin', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            
            return [
                // 120 requests per minute for authenticated admin users
                Limit::perMinute(120)->by($key)->response(function () {
                    return response()->json([
                        'message' => 'Too many admin requests. Please slow down.',
                        'retry_after' => 60
                    ], 429);
                }),
                
                // 500 requests per hour
                Limit::perHour(500)->by($key),
            ];
        });
        
        // Authentication rate limiting - prevent brute force
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = strtolower($request->input('email')).'|'.$request->ip();
            
            return [
                // 5 attempts per minute
                Limit::perMinute(5)->by($throttleKey)->response(function () {
                    return back()->withErrors([
                        'email' => 'Too many login attempts. Please try again in 1 minute.',
                    ]);
                }),
                
                // 20 attempts per hour
                Limit::perHour(20)->by($throttleKey),
            ];
        });
        
        // API rate limiting - if API routes are added in future
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        
        // Global rate limiting for all routes
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(1000)->by($request->ip());
        });
    }
    
    /**
     * Configure route model binding.
     */
    protected function configureRouteModelBinding(): void
    {
        // Ensure User model binding includes soft deleted check if needed
        Route::model('user', User::class);
        
        // Custom binding for user parameters with additional checks
        Route::bind('user', function (string $value, \Illuminate\Routing\Route $route) {
            $user = User::where('id', $value)->first();
            
            if (!$user) {
                abort(404, 'User not found.');
            }
            
            // Additional security: prevent access to super admin users by non-super-admins
            if ($user->hasRole('Super Admin') && !auth()->user()?->hasRole('Super Admin')) {
                abort(403, 'Access denied to this user record.');
            }
            
            return $user;
        });
    }
    
    /**
     * Load the application routes.
     */
    protected function loadRoutes(): void
    {
        // Routes are loaded in bootstrap/app.php in Laravel 11
        // This method is kept for potential future route loading logic
    }
}
