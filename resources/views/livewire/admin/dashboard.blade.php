<div>
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview of your admin panel')

<!-- Enhanced Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users Card -->
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between p-6">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUsers) }}</p>
                @if($userGrowthPercentage > 0)
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">+{{ $userGrowthPercentage }}% this week</p>
                @else
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $activeUsers }} active users</p>
                @endif
            </div>
            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
            </div>
        </div>
    </x-card>

    <!-- Active Users Card -->
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between p-6">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Users</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($activeUsers) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $verifiedUsers }} verified</p>
            </div>
            <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </x-card>

    <!-- Roles Card -->
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between p-6">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">System Roles</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalRoles) }}</p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">{{ $totalPermissions }} permissions</p>
            </div>
            <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
        </div>
    </x-card>

    <!-- Activities Today Card -->
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between p-6">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today's Activity</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($todayActivities) }}</p>
                @if($activityGrowthPercentage > 0)
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">+{{ $activityGrowthPercentage }}% this week</p>
                @else
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ number_format($totalActivities) }} total</p>
                @endif
            </div>
            <div class="w-12 h-12 bg-orange-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
        </div>
    </x-card>
</div>

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
                                <div class="bg-{{ $role['color'] }}-500 h-2 rounded-full" style="width: {{ $totalUsers > 0 ? ($role['count'] / $totalUsers) * 100 : 0 }}%"></div>
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
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Growth Statistics</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Weekly Growth</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">+{{ $weeklyUserGrowth }} users</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Monthly Growth</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">+{{ $monthlyUserGrowth }} users</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Weekly Activities</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($weeklyActivities) }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Inactive Users</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $inactiveUsers }}</span>
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