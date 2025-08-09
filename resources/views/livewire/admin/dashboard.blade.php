<div>
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview of your admin panel')

<!-- Dashboard Controls -->
<div class="flex justify-between items-center mb-6">
    <div class="flex space-x-4">
        <x-button wire:click="refreshDashboard" secondary icon="arrow-path" spinner="refreshDashboard">
            Refresh
        </x-button>
        @if($showCharts)
            <x-button wire:click="toggleCharts" primary icon="chart-bar">
                Hide Charts
            </x-button>
        @else
            <x-button wire:click="toggleCharts" secondary icon="chart-bar">
                Show Charts
            </x-button>
        @endif
    </div>
    
    <div class="text-sm text-gray-500 dark:text-gray-400">
        Last updated: {{ now()->format('M d, Y H:i') }}
    </div>
</div>

<!-- Enhanced Statistics Cards using new component -->
<div class="mb-8">
    <livewire:admin.components.dashboard-stats />
</div>

<!-- Interactive Charts Section -->
@if($showCharts)
<div class="mb-8">
    <livewire:admin.components.dashboard-charts />
</div>
@endif

<!-- Role Distribution and Quick Stats -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Role Distribution Card -->
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Role Distribution</h3>
            <div class="space-y-4">
                @foreach($roleDistribution as $role)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded-full bg-{{ $role['color'] }}-500"></div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $role['name'] }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $role['count'] }} users</span>
                            <div class="w-20 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="bg-{{ $role['color'] }}-500 h-2 rounded-full" style="width: {{ $quickStats['total_users'] > 0 ? ($role['count'] / $quickStats['total_users']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-card>

    <!-- Quick Stats Card -->
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Statistics</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Users</span>
                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ number_format($quickStats['total_users']) }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Active Users</span>
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ number_format($quickStats['active_users']) }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">System Roles</span>
                    <span class="text-sm font-medium text-purple-600 dark:text-purple-400">{{ number_format($quickStats['total_roles']) }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Today's Activities</span>
                    <span class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ number_format($quickStats['today_activities']) }}</span>
                </div>
            </div>
        </div>
    </x-card>
</div>

<!-- Recent Activities Section -->
<div class="mb-8">
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Activities</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($recentActivities as $activity)
                    <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex-shrink-0">
                            @if($activity['log_name'] === 'default')
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            @else
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $activity['description'] }}</p>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-xs text-gray-500 dark:text-gray-400">by {{ $activity['causer_name'] }}</span>
                                @if($activity['subject_type'])
                                    <span class="text-xs text-gray-400">•</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $activity['subject_type'] }}</span>
                                @endif
                                <span class="text-xs text-gray-400">•</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400" title="{{ $activity['created_at_full'] }}">{{ $activity['created_at'] }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">No recent activities</p>
                    </div>
                @endforelse
            </div>
            @if(count($recentActivities) > 0)
                <div class="mt-6 text-center border-t border-gray-200 dark:border-gray-700 pt-4">
                    @can('activity-logs.view')
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                            View All Activities →
                        </a>
                    @else
                        <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ count($recentActivities) }} recent activities</span>
                    @endcan
                </div>
            @endif
        </div>
    </x-card>
</div>
</div>