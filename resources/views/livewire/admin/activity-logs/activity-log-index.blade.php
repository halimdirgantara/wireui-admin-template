<div>
@section('page-title', 'Activity Logs')
@section('page-description', 'View and manage system activity logs and audit trails')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Activity Logs</span>
        </div>
    </li>
@endsection

<div class="space-y-6">
    <!-- Filters and Search -->
    <div class="flat-card p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-icon name="magnifying-glass" class="w-4 h-4 text-gray-400" />
                    </div>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search activities, users, descriptions..." 
                           class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
            
            <!-- Log Name/Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Activity Type</label>
                <select wire:model.live="logName" 
                        class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Types</option>
                    @foreach($logNames as $logName)
                        <option value="{{ $logName }}">{{ ucfirst(str_replace('_', ' ', $logName)) }}</option>
                    @endforeach
                </select>
            </div>
            
            @if(!$hasCustomActivities)
                <!-- Causer Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">User Type</label>
                    <select wire:model.live="causerType" 
                            class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All User Types</option>
                        @foreach($causerTypes as $type)
                            <option value="App\Models\{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Subject Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subject Type</label>
                    <select wire:model.live="subjectType" 
                            class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Subject Types</option>
                        @foreach($subjectTypes as $type)
                            <option value="App\Models\{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                <input type="date" 
                       wire:model.live="dateFrom"
                       class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                <input type="date" 
                       wire:model.live="dateTo"
                       class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>
        
        <!-- Filter Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-2">
                @if($search || $logName || $causerType || $subjectType || $dateFrom || $dateTo)
                    <button wire:click="clearFilters" 
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                        <x-icon name="x-mark" class="w-4 h-4 mr-1" />
                        Clear Filters
                    </button>
                @endif
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Per Page -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">Show:</label>
                    <select wire:model.live="perPage" 
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                    <span class="text-sm text-gray-600 dark:text-gray-400">entries</span>
                </div>
                
                <!-- Export -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" 
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
                        <x-icon name="arrow-down-tray" class="w-4 h-4 mr-2" />
                        Export
                        <x-icon name="chevron-down" class="w-4 h-4 ml-1" />
                    </button>
                    
                    <div x-show="open" 
                         @click.outside="open = false"
                         x-transition
                         class="absolute right-0 z-10 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700">
                        <div class="py-1">
                            <button wire:click="exportActivities('csv')" 
                                    @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Export as CSV
                            </button>
                            <button wire:click="exportActivities('xlsx')" 
                                    @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Export as Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="flat-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('created_at')" 
                                    class="flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                <span>Date & Time</span>
                                @if($sortBy === 'created_at')
                                    <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                @else
                                    <x-icon name="chevron-up-down" class="w-4 h-4 opacity-50" />
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</span>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy({{ $hasCustomActivities ? "'type'" : "'log_name'" }})" 
                                    class="flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                <span>Activity Type</span>
                                @if($sortBy === ($hasCustomActivities ? 'type' : 'log_name'))
                                    <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                @else
                                    <x-icon name="chevron-up-down" class="w-4 h-4 opacity-50" />
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('description')" 
                                    class="flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                <span>Description</span>
                                @if($sortBy === 'description')
                                    <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                                @else
                                    <x-icon name="chevron-up-down" class="w-4 h-4 opacity-50" />
                                @endif
                            </button>
                        </th>
                        @if(!$hasCustomActivities)
                            <th class="px-6 py-3 text-left">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</span>
                            </th>
                        @else
                            <th class="px-6 py-3 text-left">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Metadata</span>
                            </th>
                        @endif
                        <th class="px-6 py-3 text-left">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($activities as $activity)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <!-- Date & Time -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white font-medium">
                                    {{ $activity->created_at->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $activity->created_at->format('H:i:s') }}
                                </div>
                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $activity->created_at->diffForHumans() }}
                                </div>
                            </td>
                            
                            <!-- User -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($hasCustomActivities && $activity->user)
                                    <div class="flex items-center">
                                        <img src="{{ $activity->user->avatar_url }}" 
                                             alt="{{ $activity->user->name }}" 
                                             class="w-8 h-8 rounded-full mr-3">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                @if($search)
                                                    {!! $activity->highlightSearchResults($activity->user->name, $search) !!}
                                                @else
                                                    {{ $activity->user->name }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $activity->user->email }}
                                            </div>
                                        </div>
                                    </div>
                                @elseif(!$hasCustomActivities && $activity->causer)
                                    <div class="flex items-center">
                                        <img src="{{ $activity->causer->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($activity->causer->name) . '&background=3b82f6&color=ffffff' }}" 
                                             alt="{{ $activity->causer->name }}" 
                                             class="w-8 h-8 rounded-full mr-3">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                @if($search)
                                                    {!! str_replace($search, '<mark class="bg-yellow-200 dark:bg-yellow-800">' . $search . '</mark>', $activity->causer->name) !!}
                                                @else
                                                    {{ $activity->causer->name }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $activity->causer->email ?? class_basename($activity->causer_type) }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-full mr-3 flex items-center justify-center">
                                            <x-icon name="cog-6-tooth" class="w-4 h-4 text-gray-500" />
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                System
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            
                            <!-- Activity Type -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $activityType = $hasCustomActivities ? $activity->type : $activity->log_name;
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900/50 text-primary-800 dark:text-primary-200">
                                    @if($search)
                                        @if($hasCustomActivities)
                                            {!! $activity->highlightSearchResults($activityType, $search) !!}
                                        @else
                                            {!! str_replace($search, '<mark class="bg-yellow-200 dark:bg-yellow-800">' . $search . '</mark>', $activityType) !!}
                                        @endif
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $activityType)) }}
                                    @endif
                                </span>
                            </td>
                            
                            <!-- Description -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-white max-w-xs truncate" 
                                     title="{{ $activity->description }}">
                                    @if($search)
                                        @if($hasCustomActivities)
                                            {!! $activity->highlightSearchResults($activity->description, $search) !!}
                                        @else
                                            {!! str_replace($search, '<mark class="bg-yellow-200 dark:bg-yellow-800">' . $search . '</mark>', $activity->description) !!}
                                        @endif
                                    @else
                                        {{ $activity->description }}
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Subject/Metadata -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($hasCustomActivities)
                                    @if($activity->meta && is_array($activity->meta) && count($activity->meta) > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                            {{ count($activity->meta) }} field{{ count($activity->meta) !== 1 ? 's' : '' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">No metadata</span>
                                    @endif
                                @else
                                    @if($activity->subject_type)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                            {{ class_basename($activity->subject_type) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">No subject</span>
                                    @endif
                                @endif
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- View Details Button -->
                                    <button class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
                                            title="View Details"
                                            x-data
                                            @click="$dispatch('open-activity-details', { activity: @json($activity) })">
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $hasCustomActivities ? '6' : '6' }}" class="px-6 py-12 text-center">
                                <div class="max-w-md mx-auto">
                                    <x-icon name="clipboard-document-list" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">No activities found</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @if($search || $logName || $causerType || $subjectType || $dateFrom || $dateTo)
                                            Try adjusting your search terms or filters.
                                        @else
                                            Activity logs will appear here as users interact with the system.
                                        @endif
                                    </p>
                                    
                                    @if($search || $logName || $causerType || $subjectType || $dateFrom || $dateTo)
                                        <button wire:click="clearFilters" 
                                                class="mt-4 inline-flex items-center px-3 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                                            Clear filters
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($activities->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $activities->links() }}
            </div>
        @endif
    </div>

    <!-- Activity Count Summary -->
    @if($activities->total() > 0)
        <div class="flat-card p-4">
            <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                <div>
                    Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ number_format($activities->total()) }} activities
                </div>
                <div class="flex items-center space-x-4">
                    @if($hasCustomActivities)
                        <span class="inline-flex items-center">
                            <span class="w-2 h-2 bg-primary-500 rounded-full mr-2"></span>
                            Custom Activity System
                        </span>
                    @else
                        <span class="inline-flex items-center">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                            Spatie Activity Log
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Activity Details Modal (placeholder for future implementation) -->
<div x-data="{ showDetails: false, activityDetails: null }"
     @open-activity-details.window="showDetails = true; activityDetails = $event.detail.activity"
     x-show="showDetails"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div x-show="showDetails" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showDetails = false"
             class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

        <!-- Modal panel -->
        <div x-show="showDetails"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Activity Details</h3>
                <button @click="showDetails = false" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <x-icon name="x-mark" class="w-6 h-6" />
                </button>
            </div>
            
            <div class="space-y-4 text-sm">
                <template x-if="activityDetails">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Activity details will be displayed here.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
</div>