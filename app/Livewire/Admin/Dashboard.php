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
    { // Calculate statistics
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

        return view('livewire.admin.dashboard', compact(
            'totalUsers',
            'totalRoles',
            'totalPermissions',
            'totalActivities',
            'todayActivities',
            'recentActivities'
        ));
    }
}
