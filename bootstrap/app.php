<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\RouteServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global security headers middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        
        // Register custom middleware aliases
        $middleware->alias([
            'admin.access' => \App\Http\Middleware\AdminAccessMiddleware::class,
        ]);
        
        // Web middleware group enhancements
        $middleware->web(append: [
            // Additional web-specific middleware if needed
        ]);
        
        // API rate limiting
        $middleware->throttleApi();
        
        // Note: Redis-based rate limiting is available when Redis is configured
        // For now, using default file-based rate limiting which works without Redis
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
