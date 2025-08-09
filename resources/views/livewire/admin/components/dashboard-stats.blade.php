<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Total Users Card -->
    <x-card class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-700/50 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Total Users</p>
                    <p class="text-3xl font-bold text-blue-900 dark:text-blue-100 mt-1">
                        {{ number_format($stats['users']['total']) }}
                    </p>
                    <div class="flex items-center mt-2 space-x-2">
                        @if($stats['users']['weekly_growth_percentage'] > 0)
                            <span class="inline-flex items-center text-xs font-medium text-green-700 dark:text-green-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                                </svg>
                                +{{ $stats['users']['weekly_growth_percentage'] }}%
                            </span>
                        @elseif($stats['users']['weekly_growth_percentage'] < 0)
                            <span class="inline-flex items-center text-xs font-medium text-red-700 dark:text-red-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                                </svg>
                                {{ $stats['users']['weekly_growth_percentage'] }}%
                            </span>
                        @else
                            <span class="text-xs text-gray-600 dark:text-gray-400">No change</span>
                        @endif
                        <span class="text-xs text-blue-600 dark:text-blue-400">this week</span>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 w-14 h-14 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-blue-600/10 dark:bg-blue-900/20 px-6 py-3 border-t border-blue-200 dark:border-blue-700/50">
            <div class="flex justify-between text-xs">
                <span class="text-blue-700 dark:text-blue-300">Active: {{ number_format($stats['users']['active']) }}</span>
                <span class="text-blue-600 dark:text-blue-400">{{ $stats['users']['verification_rate'] }}% verified</span>
            </div>
        </div>
    </x-card>

    <!-- Active Users Card -->
    <x-card class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700/50 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-700 dark:text-green-300">Active Users</p>
                    <p class="text-3xl font-bold text-green-900 dark:text-green-100 mt-1">
                        {{ number_format($stats['users']['active']) }}
                    </p>
                    <div class="mt-2">
                        <span class="text-xs text-green-600 dark:text-green-400">
                            {{ $stats['users']['active_rate'] }}% of total users
                        </span>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 w-14 h-14 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-green-600/10 dark:bg-green-900/20 px-6 py-3 border-t border-green-200 dark:border-green-700/50">
            <div class="flex justify-between text-xs">
                <span class="text-green-700 dark:text-green-300">Verified: {{ number_format($stats['users']['verified']) }}</span>
                <span class="text-red-600 dark:text-red-400">Inactive: {{ number_format($stats['users']['inactive']) }}</span>
            </div>
        </div>
    </x-card>

    <!-- System Roles Card -->
    <x-card class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-700/50 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-purple-700 dark:text-purple-300">System Roles</p>
                    <p class="text-3xl font-bold text-purple-900 dark:text-purple-100 mt-1">
                        {{ number_format($stats['system']['total_roles']) }}
                    </p>
                    <div class="mt-2">
                        <span class="text-xs text-purple-600 dark:text-purple-400">
                            {{ $stats['system']['total_permissions'] }} permissions
                        </span>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 w-14 h-14 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-purple-600/10 dark:bg-purple-900/20 px-6 py-3 border-t border-purple-200 dark:border-purple-700/50">
            <div class="flex justify-between text-xs">
                <span class="text-purple-700 dark:text-purple-300">RBAC System</span>
                <span class="text-purple-600 dark:text-purple-400">{{ number_format(($stats['system']['total_roles'] * $stats['system']['total_permissions'])) }} combinations</span>
            </div>
        </div>
    </x-card>

    <!-- Activities Today Card -->
    <x-card class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 border border-orange-200 dark:border-orange-700/50 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-orange-700 dark:text-orange-300">Today's Activity</p>
                    <p class="text-3xl font-bold text-orange-900 dark:text-orange-100 mt-1">
                        {{ number_format($stats['activities']['today']) }}
                    </p>
                    <div class="flex items-center mt-2 space-x-2">
                        @if($stats['activities']['growth_percentage'] > 0)
                            <span class="inline-flex items-center text-xs font-medium text-green-700 dark:text-green-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                                </svg>
                                +{{ $stats['activities']['growth_percentage'] }}%
                            </span>
                        @elseif($stats['activities']['growth_percentage'] < 0)
                            <span class="inline-flex items-center text-xs font-medium text-red-700 dark:text-red-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                                </svg>
                                {{ $stats['activities']['growth_percentage'] }}%
                            </span>
                        @else
                            <span class="text-xs text-gray-600 dark:text-gray-400">No change</span>
                        @endif
                        <span class="text-xs text-orange-600 dark:text-orange-400">vs last week</span>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 w-14 h-14 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-orange-600/10 dark:bg-orange-900/20 px-6 py-3 border-t border-orange-200 dark:border-orange-700/50">
            <div class="flex justify-between text-xs">
                <span class="text-orange-700 dark:text-orange-300">This week: {{ number_format($stats['activities']['weekly']) }}</span>
                <span class="text-orange-600 dark:text-orange-400">{{ $stats['activities']['avg_per_user'] }} avg/user</span>
            </div>
        </div>
    </x-card>

    <!-- Refresh Button -->
    <div class="md:col-span-2 lg:col-span-4 flex justify-end mt-4">
        <x-button 
            wire:click="refreshStats" 
            size="sm" 
            secondary 
            icon="arrow-path"
            spinner="refreshStats"
        >
            Refresh Stats
        </x-button>
    </div>
</div>
