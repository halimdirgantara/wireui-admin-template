<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

class Dashboard extends Component
{
    #[Layout('layouts.admin')]
    public function render()
    {
        // User Statistics
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        
        // Recent user growth (last 7 days)
        $weeklyUserGrowth = User::where('created_at', '>=', Carbon::now()->subWeek())->count();
        $monthlyUserGrowth = User::where('created_at', '>=', Carbon::now()->subMonth())->count();
        
        // System Statistics
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        
        // Activity Statistics
        $totalActivities = Activity::count();
        $todayActivities = Activity::whereDate('created_at', Carbon::today())->count();
        $weeklyActivities = Activity::where('created_at', '>=', Carbon::now()->subWeek())->count();
        
        // Role Distribution
        $roleDistribution = Role::withCount('users')
            ->orderBy('users_count', 'desc')
            ->get()
            ->map(function ($role) {
                return [
                    'name' => $role->name,
                    'count' => $role->users_count,
                    'color' => $this->getRoleColor($role->name)
                ];
            });

        // Recent activities with better formatting
        $recentActivities = Activity::with(['causer', 'subject'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description ?? 'Activity performed',
                    'causer_name' => $activity->causer?->name ?? 'System',
                    'subject_type' => class_basename($activity->subject_type ?? ''),
                    'created_at' => $activity->created_at->diffForHumans(),
                    'created_at_full' => $activity->created_at->format('M d, Y H:i'),
                    'log_name' => $activity->log_name ?? 'default',
                ];
            });

        // Calculate growth percentages
        $userGrowthPercentage = $totalUsers > 0 ? round(($weeklyUserGrowth / $totalUsers) * 100, 1) : 0;
        $activityGrowthPercentage = $totalActivities > 0 ? round(($weeklyActivities / $totalActivities) * 100, 1) : 0;

        return view('livewire.admin.dashboard', compact(
            'totalUsers',
            'activeUsers', 
            'inactiveUsers',
            'verifiedUsers',
            'weeklyUserGrowth',
            'monthlyUserGrowth',
            'userGrowthPercentage',
            'totalRoles',
            'totalPermissions',
            'totalActivities',
            'todayActivities',
            'weeklyActivities',
            'activityGrowthPercentage',
            'roleDistribution',
            'recentActivities'
        ));
    }

    private function getRoleColor($roleName)
    {
        $colors = [
            'Super Admin' => 'purple',
            'Admin' => 'blue', 
            'Editor' => 'green',
            'Viewer' => 'gray'
        ];
        
        return $colors[$roleName] ?? 'gray';
    }
}
