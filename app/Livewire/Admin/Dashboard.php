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
    public $showCharts = true;
    public $refreshInterval = 30000; // 30 seconds in milliseconds
    
    #[Layout('layouts.admin')]
    public function render()
    {
        // Quick stats for legacy support and overview
        $quickStats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_roles' => Role::count(),
            'today_activities' => Activity::whereDate('created_at', Carbon::today())->count(),
        ];
        
        // Role Distribution for existing view compatibility
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

        return view('livewire.admin.dashboard', compact(
            'quickStats',
            'roleDistribution',
            'recentActivities'
        ));
    }

    public function toggleCharts()
    {
        $this->showCharts = !$this->showCharts;
    }
    
    public function refreshDashboard()
    {
        // This will trigger a re-render of all components
        $this->dispatch('dashboard-refreshed');
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
