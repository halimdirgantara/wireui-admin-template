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

<!-- Blog Statistics (if user has blog permissions) -->
@if(count($blogStats) > 0)
<div class="mb-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Posts -->
        <x-card class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 border border-blue-200/50 dark:border-blue-700/50">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <x-icon name="document-text" class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Posts</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($blogStats['total_posts']) }}</p>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Published Posts -->
        <x-card class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 border border-green-200/50 dark:border-green-700/50">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                            <x-icon name="eye" class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">Published</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ number_format($blogStats['published_posts']) }}</p>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Categories -->
        <x-card class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 border border-purple-200/50 dark:border-purple-700/50">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                            <x-icon name="rectangle-stack" class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Categories</p>
                        <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ number_format($blogStats['total_categories']) }}</p>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Total Views -->
        <x-card class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/30 border border-orange-200/50 dark:border-orange-700/50">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                            <x-icon name="chart-bar" class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-orange-600 dark:text-orange-400">Total Views</p>
                        <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">{{ number_format($blogStats['total_views']) }}</p>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endif

<!-- Role Distribution and Quick Stats -->
<div class="grid grid-cols-1 lg:grid-cols-{{ count($blogStats) > 0 ? '3' : '2' }} gap-6 mb-8">
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

    <!-- Blog Summary Card (if user has blog permissions) -->
    @if(count($blogStats) > 0)
        <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Blog Overview</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Draft Posts</span>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ number_format($blogStats['draft_posts']) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Scheduled Posts</span>
                        <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ number_format($blogStats['scheduled_posts']) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Active Tags</span>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ number_format($blogStats['active_tags']) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Posts Today</span>
                        <span class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ number_format($blogStats['today_posts']) }}</span>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    @can('posts.create')
                        <a href="{{ route('admin.blog.posts.create') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                            <x-icon name="plus" class="w-4 h-4 mr-1" />
                            Create New Post
                        </a>
                    @endcan
                </div>
            </div>
        </x-card>
    @endif
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

<!-- Recent Blog Posts (if user has blog permissions) -->
@if(count($recentPosts) > 0)
<div class="mb-8">
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Blog Posts</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($recentPosts as $post)
                    <div class="flex items-start space-x-4 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        @if($post->featured_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" 
                                 alt="{{ $post->title }}" 
                                 class="w-16 h-12 object-cover rounded-lg flex-shrink-0">
                        @else
                            <div class="w-16 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                <x-icon name="document-text" class="w-6 h-6 text-gray-400" />
                            </div>
                        @endif
                        
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                <a href="{{ route('admin.blog.posts.edit', $post) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $post->title }}
                                </a>
                                @if($post->is_featured)
                                    <x-icon name="star" class="w-3 h-3 text-yellow-500 inline ml-1" solid />
                                @endif
                            </h4>
                            
                            @if($post->excerpt)
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                    {{ $post->excerpt }}
                                </p>
                            @endif
                            
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                    @if($post->status === 'published') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($post->status === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                    @elseif($post->status === 'scheduled') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                    @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @endif">
                                    {{ $post->status_label }}
                                </span>
                                
                                <span class="text-xs text-gray-500 dark:text-gray-400">by {{ $post->user->name }}</span>
                                
                                @if($post->category)
                                    <span class="text-xs text-gray-400">•</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $post->category->name }}</span>
                                @endif
                                
                                <span class="text-xs text-gray-400">•</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $post->created_at->format('M d, Y') }}</span>
                                
                                @if($post->views_count)
                                    <span class="text-xs text-gray-400">•</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($post->views_count) }} views</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6 text-center border-t border-gray-200 dark:border-gray-700 pt-4">
                @can('posts.view')
                    <a href="{{ route('admin.blog.posts.index') }}" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                        View All Posts →
                    </a>
                @else
                    <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ count($recentPosts) }} recent posts</span>
                @endcan
            </div>
        </div>
    </x-card>
</div>
@endif
</div>