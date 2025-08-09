<?php

namespace App\Livewire\Admin\Components;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;

class DashboardCharts extends Component
{
    public $chartData = [];
    public $chartType = 'registration'; // 'registration', 'role_distribution', 'activity_trend'
    
    public function mount()
    {
        $this->loadChartData();
    }
    
    public function loadChartData()
    {
        $this->chartData = [
            'registration_trend' => $this->getRegistrationTrendData(),
            'role_distribution' => $this->getRoleDistributionData(),
            'activity_trend' => $this->getActivityTrendData(),
            'monthly_comparison' => $this->getMonthlyComparisonData(),
        ];
    }
    
    private function getRegistrationTrendData()
    {
        // Get last 30 days registration data
        $days = [];
        $registrations = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('M d');
            
            $count = User::whereDate('created_at', $date->toDateString())->count();
            $registrations[] = $count;
        }
        
        return [
            'labels' => $days,
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $registrations,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 2,
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ]
            ]
        ];
    }
    
    private function getRoleDistributionData()
    {
        $roles = Role::withCount('users')->get();
        $colors = [
            'rgba(139, 69, 19, 0.8)',   // Brown for Super Admin
            'rgba(59, 130, 246, 0.8)',  // Blue for Admin
            'rgba(16, 185, 129, 0.8)',  // Green for Editor
            'rgba(107, 114, 128, 0.8)', // Gray for Viewer
            'rgba(245, 101, 101, 0.8)', // Red for additional roles
            'rgba(168, 85, 247, 0.8)',  // Purple for additional roles
        ];
        
        $borderColors = [
            'rgba(139, 69, 19, 1)',
            'rgba(59, 130, 246, 1)',
            'rgba(16, 185, 129, 1)',
            'rgba(107, 114, 128, 1)',
            'rgba(245, 101, 101, 1)',
            'rgba(168, 85, 247, 1)',
        ];
        
        return [
            'labels' => $roles->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Users per Role',
                    'data' => $roles->pluck('users_count')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $roles->count()),
                    'borderColor' => array_slice($borderColors, 0, $roles->count()),
                    'borderWidth' => 2,
                    'hoverOffset' => 4,
                ]
            ]
        ];
    }
    
    private function getActivityTrendData()
    {
        // Get last 7 days activity data
        $days = [];
        $activities = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('D');
            
            $count = Activity::whereDate('created_at', $date->toDateString())->count();
            $activities[] = $count;
        }
        
        return [
            'labels' => $days,
            'datasets' => [
                [
                    'label' => 'Daily Activities',
                    'data' => $activities,
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                    ],
                    'borderColor' => [
                        'rgba(245, 158, 11, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(14, 165, 233, 1)',
                    ],
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                    'borderSkipped' => false,
                ]
            ]
        ];
    }
    
    private function getMonthlyComparisonData()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        $currentMonthUsers = User::whereBetween('created_at', [
            $currentMonth,
            Carbon::now()
        ])->count();
        
        $lastMonthUsers = User::whereBetween('created_at', [
            $lastMonth,
            $lastMonth->copy()->endOfMonth()
        ])->count();
        
        $currentMonthActivities = Activity::whereBetween('created_at', [
            $currentMonth,
            Carbon::now()
        ])->count();
        
        $lastMonthActivities = Activity::whereBetween('created_at', [
            $lastMonth,
            $lastMonth->copy()->endOfMonth()
        ])->count();
        
        return [
            'labels' => [
                $lastMonth->format('M Y'),
                $currentMonth->format('M Y')
            ],
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => [$lastMonthUsers, $currentMonthUsers],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Activities',
                    'data' => [$lastMonthActivities, $currentMonthActivities],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'borderWidth' => 2,
                ]
            ]
        ];
    }
    
    public function switchChart($type)
    {
        $this->chartType = $type;
    }
    
    public function refreshCharts()
    {
        $this->loadChartData();
        $this->dispatch('charts-refreshed');
    }
    
    public function render()
    {
        return view('livewire.admin.components.dashboard-charts');
    }
}
