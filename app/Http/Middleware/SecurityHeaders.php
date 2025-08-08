<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security Headers for Production
        if (app()->environment('production')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
            
            // HSTS (HTTP Strict Transport Security)
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            
            // Content Security Policy - restrictive for admin panel
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-eval' 'unsafe-inline'", // Livewire needs unsafe-eval
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com",
                "img-src 'self' data: https: blob:",
                "connect-src 'self'",
                "frame-src 'none'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'"
            ];
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }
}