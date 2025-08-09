@section('page-title', 'Search Results')
@section('page-description', 'Search results for "' . $q . '"')

<div class="space-y-6">
    <!-- Search Header -->
    <div class="flat-card p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    @if($totalResults > 0)
                        {{ number_format($totalResults) }} result{{ $totalResults !== 1 ? 's' : '' }} for "{{ $q }}"
                    @else
                        No results found for "{{ $q }}"
                    @endif
                </h3>
                
                @if($totalResults > 0)
                    <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-600 dark:text-gray-400">
                        @foreach($resultsByType as $type => $count)
                            @if($count > 0)
                                <span class="inline-flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-primary-500 mr-2"></span>
                                    {{ ucfirst($type) }}: {{ $count }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            @if($totalResults > 0)
                <div class="flex items-center space-x-4">
                    <!-- Type Filter -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Type:</label>
                        <select wire:model.live="type" 
                                class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-primary-500 focus:border-primary-500">
                            @foreach($availableTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort Options -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort:</label>
                        <select wire:model.live="sortBy" 
                                class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-primary-500 focus:border-primary-500">
                            @foreach($sortOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        
                        <button wire:click="$set('sortDirection', sortDirection === 'asc' ? 'desc' : 'asc')" 
                                class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <x-icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($totalResults > 0)
        <!-- Users Results -->
        @if(($type === 'all' || $type === 'users') && $users->count() > 0)
            <div class="flat-card">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <x-icon name="users" class="w-5 h-5 mr-2 text-primary-500" />
                        Users
                        @if($type === 'all')
                            <span class="ml-2 text-sm text-gray-500">({{ $users->count() }} shown)</span>
                        @endif
                    </h4>
                </div>
                
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($users as $user)
                        <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <img src="{{ $user->avatar_url }}" 
                                         alt="{{ $user->name }}" 
                                         class="w-10 h-10 rounded-full">
                                    
                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {!! (new \App\Models\User())->highlightSearchResults($user->name, $q) !!}
                                        </h5>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                            {!! (new \App\Models\User())->highlightSearchResults($user->email, $q) !!}
                                        </p>
                                        @if($user->roles->count() > 0)
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach($user->roles as $role)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-100">
                                                        {{ $role->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                            Inactive
                                        </span>
                                    @endif
                                    
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-150">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($type === 'users' && $users->hasPages())
                    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Activities Results -->
        @if(($type === 'all' || $type === 'activities') && $activities->count() > 0)
            <div class="flat-card">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <x-icon name="clock" class="w-5 h-5 mr-2 text-primary-500" />
                        Activities
                        @if($type === 'all')
                            <span class="ml-2 text-sm text-gray-500">({{ $activities->count() }} shown)</span>
                        @endif
                    </h4>
                </div>
                
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($activities as $activity)
                        <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150">
                            <div class="flex items-start space-x-4">
                                @if($activity->user)
                                    <img src="{{ $activity->user->avatar_url }}" 
                                         alt="{{ $activity->user->name }}" 
                                         class="w-8 h-8 rounded-full flex-shrink-0">
                                @else
                                    <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center flex-shrink-0">
                                        <x-icon name="user" class="w-4 h-4 text-gray-500" />
                                    </div>
                                @endif
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h5 class="text-sm font-medium text-gray-900 dark:text-white">
                                            {!! (new \App\Models\Activity())->highlightSearchResults($activity->type, $q) !!}
                                        </h5>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                        {!! (new \App\Models\Activity())->highlightSearchResults($activity->description, $q) !!}
                                    </p>
                                    
                                    @if($activity->user)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            by {{ $activity->user->name }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($type === 'activities' && method_exists($activities, 'hasPages') && $activities->hasPages())
                    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        @endif

        @if($type === 'all' && ($users->count() > 0 || $activities->count() > 0))
            <div class="flex justify-center">
                <div class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        Showing partial results. Use filters above to see more specific results.
                    </p>
                </div>
            </div>
        @endif

    @else
        <!-- No Results -->
        <div class="flat-card p-12 text-center">
            <div class="max-w-md mx-auto">
                <x-icon name="magnifying-glass" class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No results found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    We couldn't find anything matching "{{ $q }}". Try adjusting your search terms or filters.
                </p>
                
                <div class="text-sm text-gray-500 dark:text-gray-400 space-y-2">
                    <p class="font-medium">Search tips:</p>
                    <ul class="text-left space-y-1 max-w-xs mx-auto">
                        <li>• Try different keywords</li>
                        <li>• Check your spelling</li>
                        <li>• Use more general terms</li>
                        <li>• Try searching for partial words</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>