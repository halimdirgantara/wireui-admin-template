<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            Log::warning('Unauthorized admin access attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
            ]);
            
            return redirect()->route('login')
                ->with('error', 'You must be logged in to access the admin panel.');
        }
        
        $user = Auth::user();
        
        // Check if user has any admin role or permissions
        if (!$this->hasAdminAccess($user)) {
            Log::warning('Admin access denied for user', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip(),
                'route' => $request->route()?->getName(),
                'roles' => $user->roles->pluck('name')->toArray(),
            ]);
            
            // Log this as an activity
            activity('admin_access')
                ->causedBy($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'route' => $request->route()?->getName(),
                    'attempted_at' => now(),
                ])
                ->log('Attempted unauthorized admin access');
                
            abort(403, 'Access denied. You do not have permission to access the admin panel.');
        }
        
        // Check if user account is active
        if (!$user->is_active) {
            Log::warning('Inactive user attempted admin access', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip(),
            ]);
            
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Please contact an administrator.');
        }
        
        // Log successful admin access for audit trail
        if ($request->isMethod('GET') && !$request->ajax()) {
            activity('admin_access')
                ->causedBy($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'route' => $request->route()?->getName(),
                    'accessed_at' => now(),
                ])
                ->log('Admin panel accessed');
        }
        
        return $next($request);
    }
    
    /**
     * Check if user has admin access
     */
    private function hasAdminAccess($user): bool
    {
        // Check if user has any admin-related roles
        $adminRoles = ['Super Admin', 'Admin', 'Editor'];
        
        if ($user->hasAnyRole($adminRoles)) {
            return true;
        }
        
        // Check if user has any admin permissions
        $adminPermissions = [
            'dashboard.view',
            'users.view',
            'roles.view',
            'activity-logs.view'
        ];
        
        foreach ($adminPermissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }
        
        return false;
    }
}
