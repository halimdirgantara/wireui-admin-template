<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Calculate statistics
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        $totalActivities = Activity::count();
        $todayActivities = Activity::whereDate('created_at', Carbon::today())->count();
        
        // Get recent activities
        $recentActivities = Activity::with('causer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'description' => $activity->description ?? 'Activity performed',
                    'created_at' => $activity->created_at->diffForHumans(),
                ];
            });

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalRoles', 
            'totalPermissions',
            'totalActivities',
            'todayActivities',
            'recentActivities'
        ));
    }
}