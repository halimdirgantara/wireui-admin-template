<div class="flat-card" 
     x-data="{ 
        autoRefresh: @entangle('autoRefresh'),
        refreshInterval: null,
        startAutoRefresh(interval) {
            this.stopAutoRefresh();
            this.refreshInterval = setInterval(() => {
                $wire.refresh();
            }, interval);
        },
        stopAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }
        }
     }"
     x-on:start-auto-refresh.window="startAutoRefresh($event.detail.interval)"
     x-on:stop-auto-refresh.window="stopAutoRefresh()"
     x-init="$watch('autoRefresh', value => {
        if (value) {
            startAutoRefresh({{ $refreshInterval * 1000 }});
        } else {
            stopAutoRefresh();
        }
     })">
    
    <!-- Header -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <x-icon name="clock" class="w-6 h-6 text-primary-500" />
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Activity Feed</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Recent system and user activities</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <!-- Auto Refresh Toggle -->
                <label class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                    <input type="checkbox" 
                           wire:model.live="autoRefresh"
                           class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 focus:ring-offset-0">
                    <span>Auto refresh</span>
                </label>
                
                <!-- Manual Refresh -->
                <button wire:click="refresh" 
                        class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                    <x-icon name="arrow-path" class="w-5 h-5" />
                </button>
                
                @if($showFilters)
                    <!-- Toggle Filters -->
                    <button @click="$refs.filters.classList.toggle('hidden')" 
                            class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                        <x-icon name="funnel" class="w-5 h-5" />
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($showFilters)
        <!-- Filters -->
        <div x-ref="filters" class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 space-y-4">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-64">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-icon name="magnifying-glass" class="w-4 h-4 text-gray-400" />
                        </div>
                        <input type="text" 
                               wire:model.live.debounce.300ms="search"
                               placeholder="Search activities..." 
                               class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
                
                <!-- Activity Type Filter -->
                <div class="min-w-40">
                    <select wire:model.live="filter" 
                            class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach($availableFilters as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Time Filter -->
                <div class="min-w-32">
                    <select wire:model.live="timeFilter" 
                            class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach($timeFilters as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Clear Filters -->
                @if($filter !== 'all' || $timeFilter !== 'all' || $search)
                    <button wire:click="clearFilters" 
                            class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Clear
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- Activities List -->
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($activities as $activity)
            @if($hasCustomActivities)
                <!-- Custom Activity Layout -->
                <div class="p-6 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors duration-150">
                    <div class="flex items-start space-x-4">
                        <!-- Activity Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-{{ $this->getActivityColor($activity) }}-100 dark:bg-{{ $this->getActivityColor($activity) }}-900/50 flex items-center justify-center">
                                <x-icon name="{{ $this->getActivityIcon($activity) }}" class="w-5 h-5 text-{{ $this->getActivityColor($activity) }}-600 dark:text-{{ $this->getActivityColor($activity) }}-400" />
                            </div>
                        </div>
                        
                        <!-- Activity Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        @if($search)
                                            {!! $activity->highlightSearchResults($activity->type, $search) !!}
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $activity->type)) }}
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                        @if($search)
                                            {!! $activity->highlightSearchResults($activity->description, $search) !!}
                                        @else
                                            {{ $activity->description }}
                                        @endif
                                    </p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        @if($activity->user)
                                            <div class="flex items-center space-x-1">
                                                <img src="{{ $activity->user->avatar_url }}" 
                                                     alt="{{ $activity->user->name }}" 
                                                     class="w-4 h-4 rounded-full">
                                                <span>{{ $activity->user->name }}</span>
                                            </div>
                                        @endif
                                        <span>{{ $activity->created_at->diffForHumans() }}</span>
                                        @if($activity->meta)
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-full">
                                                {{ count($activity->meta) }} detail{{ count($activity->meta) !== 1 ? 's' : '' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex-shrink-0 text-right">
                                    <time class="text-xs text-gray-500 dark:text-gray-400" 
                                          title="{{ $activity->created_at->format('M d, Y H:i:s') }}">
                                        {{ $activity->created_at->format('H:i') }}
                                    </time>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Spatie Activity Layout -->
                <div class="p-6 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors duration-150">
                    <div class="flex items-start space-x-4">
                        <!-- Activity Icon -->
                        <div class="flex-shrink-0">
                            @if($activity->causer)
                                <img src="{{ $activity->causer->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($activity->causer->name) . '&background=3b82f6&color=ffffff' }}" 
                                     alt="{{ $activity->causer->name }}" 
                                     class="w-10 h-10 rounded-full">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <x-icon name="cog-6-tooth" class="w-5 h-5 text-gray-500" />
                                </div>
                            @endif
                        </div>
                        
                        <!-- Activity Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        @if($search)
                                            {!! str_replace($search, '<mark class="bg-yellow-200 dark:bg-yellow-800">' . $search . '</mark>', $activity->description) !!}
                                        @else
                                            {{ $activity->description }}
                                        @endif
                                    </p>
                                    
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        @if($activity->causer)
                                            <span>by {{ $activity->causer->name }}</span>
                                        @else
                                            <span>by System</span>
                                        @endif
                                        
                                        <span>{{ $activity->created_at->diffForHumans() }}</span>
                                        
                                        @if($activity->log_name)
                                            <span class="px-2 py-1 bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 rounded-full">
                                                {{ $activity->log_name }}
                                            </span>
                                        @endif
                                        
                                        @if($activity->subject_type)
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-full">
                                                {{ class_basename($activity->subject_type) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex-shrink-0 text-right">
                                    <time class="text-xs text-gray-500 dark:text-gray-400" 
                                          title="{{ $activity->created_at->format('M d, Y H:i:s') }}">
                                        {{ $activity->created_at->format('H:i') }}
                                    </time>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <!-- No Activities -->
            <div class="p-12 text-center">
                <div class="max-w-sm mx-auto">
                    <x-icon name="clock" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                        @if($search || $filter !== 'all' || $timeFilter !== 'all')
                            No matching activities
                        @else
                            No activities yet
                        @endif
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @if($search || $filter !== 'all' || $timeFilter !== 'all')
                            Try adjusting your filters or search terms.
                        @else
                            Activity data will appear here as users interact with the system.
                        @endif
                    </p>
                    
                    @if($search || $filter !== 'all' || $timeFilter !== 'all')
                        <button wire:click="clearFilters" 
                                class="mt-4 inline-flex items-center px-3 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                            Clear filters
                        </button>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($activities->hasPages())
        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
            {{ $activities->links() }}
        </div>
    @endif
    
    <!-- Activity Count -->
    @if($activities->total() > 0)
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30">
            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ number_format($activities->total()) }} activities
                @if($autoRefresh)
                    <span class="inline-flex items-center ml-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-1"></span>
                        Auto-refreshing every {{ $refreshInterval }}s
                    </span>
                @endif
            </p>
        </div>
    @endif
</div>