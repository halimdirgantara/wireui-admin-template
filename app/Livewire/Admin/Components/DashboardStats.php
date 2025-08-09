<?php

namespace App\Livewire\Admin\Components;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

class DashboardStats extends Component
{
    public $stats = [];
    
    public function mount()
    {
        $this->loadStats();
    }
    
    public function loadStats()
    {
        // User Statistics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        
        // User Growth Calculations
        $weeklyUserGrowth = User::where('created_at', '>=', Carbon::now()->subWeek())->count();
        $monthlyUserGrowth = User::where('created_at', '>=', Carbon::now()->subMonth())->count();
        $yearlyUserGrowth = User::where('created_at', '>=', Carbon::now()->subYear())->count();
        
        // Previous period for comparison
        $previousWeekUsers = User::whereBetween('created_at', [
            Carbon::now()->subWeeks(2),
            Carbon::now()->subWeek()
        ])->count();
        
        $previousMonthUsers = User::whereBetween('created_at', [
            Carbon::now()->subMonths(2),
            Carbon::now()->subMonth()
        ])->count();
        
        // System Statistics
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        
        // Activity Statistics
        $totalActivities = Activity::count();
        $todayActivities = Activity::whereDate('created_at', Carbon::today())->count();
        $weeklyActivities = Activity::where('created_at', '>=', Carbon::now()->subWeek())->count();
        $monthlyActivities = Activity::where('created_at', '>=', Carbon::now()->subMonth())->count();
        
        // Previous activity periods for comparison
        $previousWeekActivities = Activity::whereBetween('created_at', [
            Carbon::now()->subWeeks(2),
            Carbon::now()->subWeek()
        ])->count();
        
        // Calculate growth percentages
        $weeklyUserGrowthPercentage = $previousWeekUsers > 0 
            ? round((($weeklyUserGrowth - $previousWeekUsers) / $previousWeekUsers) * 100, 1)
            : ($weeklyUserGrowth > 0 ? 100 : 0);
            
        $monthlyUserGrowthPercentage = $previousMonthUsers > 0 
            ? round((($monthlyUserGrowth - $previousMonthUsers) / $previousMonthUsers) * 100, 1)
            : ($monthlyUserGrowth > 0 ? 100 : 0);
            
        $activityGrowthPercentage = $previousWeekActivities > 0 
            ? round((($weeklyActivities - $previousWeekActivities) / $previousWeekActivities) * 100, 1)
            : ($weeklyActivities > 0 ? 100 : 0);
        
        // User verification rate
        $verificationRate = $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 1) : 0;
        
        // Active user rate
        $activeUserRate = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0;
        
        // Average activities per user
        $avgActivitiesPerUser = $totalUsers > 0 ? round($totalActivities / $totalUsers, 1) : 0;
        
        $this->stats = [
            'users' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'inactive' => $inactiveUsers,
                'verified' => $verifiedUsers,
                'verification_rate' => $verificationRate,
                'active_rate' => $activeUserRate,
                'weekly_growth' => $weeklyUserGrowth,
                'monthly_growth' => $monthlyUserGrowth,
                'yearly_growth' => $yearlyUserGrowth,
                'weekly_growth_percentage' => $weeklyUserGrowthPercentage,
                'monthly_growth_percentage' => $monthlyUserGrowthPercentage,
            ],
            'system' => [
                'total_roles' => $totalRoles,
                'total_permissions' => $totalPermissions,
            ],
            'activities' => [
                'total' => $totalActivities,
                'today' => $todayActivities,
                'weekly' => $weeklyActivities,
                'monthly' => $monthlyActivities,
                'growth_percentage' => $activityGrowthPercentage,
                'avg_per_user' => $avgActivitiesPerUser,
            ]
        ];
    }
    
    public function refreshStats()
    {
        $this->loadStats();
        $this->dispatch('stats-refreshed');
    }
    
    public function render()
    {
        return view('livewire.admin.components.dashboard-stats');
    }
}
