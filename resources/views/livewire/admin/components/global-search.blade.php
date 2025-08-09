<div class="relative" 
     x-data="{ showResults: @entangle('showResults') }"
     x-on:click.away="$wire.hideResults()">
    
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <x-icon name="magnifying-glass" class="w-5 h-5 text-slate-400" />
        </div>
        
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search"
            wire:keydown.escape="hideResults"
            x-on:focus="$wire.dispatch('global-search-focus')"
            placeholder="Search users, activities..." 
            class="w-full pl-10 pr-4 py-2.5 text-sm bg-white/10 backdrop-blur-sm border border-slate-200/20 rounded-lg 
                   placeholder-slate-400 text-slate-700 dark:text-slate-300 
                   focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400/50 focus:outline-none
                   transition-all duration-200 hover:bg-white/20"
        >
        
        @if($isLoading)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <div class="w-4 h-4 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
        @endif
    </div>

    <!-- Search Results Dropdown -->
    <div x-show="showResults" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         class="absolute z-50 w-full mt-2 bg-white/95 backdrop-blur-sm border border-slate-200/50 rounded-lg shadow-xl max-h-96 overflow-y-auto">
        
        @if($search && $this->totalResults > 0)
            <!-- Results Header -->
            <div class="px-4 py-3 border-b border-slate-200/50 bg-slate-50/50">
                <p class="text-sm text-slate-600 font-medium">
                    {{ $this->totalResults }} result{{ $this->totalResults !== 1 ? 's' : '' }} for "{{ $search }}"
                </p>
            </div>

            <!-- Users Section -->
            @if(isset($results['users']) && count($results['users']) > 0)
                <div class="py-2">
                    <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide bg-slate-50/30">
                        Users
                    </div>
                    @foreach($results['users'] as $user)
                        <a href="{{ $user['url'] }}" 
                           class="flex items-center px-4 py-3 hover:bg-slate-50/50 transition-colors duration-150 group">
                            <img src="{{ $user['avatar'] }}" 
                                 alt="{{ $user['title'] }}" 
                                 class="w-8 h-8 rounded-full flex-shrink-0">
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate group-hover:text-primary-600">
                                    {!! $user['highlighted_title'] !!}
                                </p>
                                <p class="text-xs text-slate-500 truncate">
                                    {!! $user['highlighted_subtitle'] !!}
                                </p>
                                @if($user['meta'])
                                    <p class="text-xs text-primary-600 font-medium">{{ $user['meta'] }}</p>
                                @endif
                            </div>
                            <x-icon name="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-primary-500" />
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Activities Section -->
            @if(isset($results['activities']) && count($results['activities']) > 0)
                <div class="py-2 border-t border-slate-200/30">
                    <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide bg-slate-50/30">
                        Activities
                    </div>
                    @foreach($results['activities'] as $activity)
                        <a href="{{ $activity['url'] }}" 
                           class="flex items-center px-4 py-3 hover:bg-slate-50/50 transition-colors duration-150 group">
                            @if($activity['avatar'])
                                <img src="{{ $activity['avatar'] }}" 
                                     alt="User" 
                                     class="w-8 h-8 rounded-full flex-shrink-0">
                            @else
                                <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center flex-shrink-0">
                                    <x-icon name="clipboard-document-list" class="w-4 h-4 text-slate-500" />
                                </div>
                            @endif
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate group-hover:text-primary-600">
                                    {!! $activity['highlighted_title'] !!}
                                </p>
                                <p class="text-xs text-slate-500 truncate">
                                    {!! $activity['highlighted_subtitle'] !!}
                                </p>
                                <p class="text-xs text-slate-400">{{ $activity['meta'] }}</p>
                            </div>
                            <x-icon name="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-primary-500" />
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- View All Results -->
            @if($this->totalResults > 0)
                <div class="px-4 py-3 border-t border-slate-200/30 bg-slate-50/30">
                    <button type="button" 
                            wire:click="$dispatch('navigate', { url: '{{ route('admin.search', ['q' => $search]) }}' })"
                            class="w-full text-center text-sm text-primary-600 hover:text-primary-700 font-medium transition-colors duration-150">
                        View all results →
                    </button>
                </div>
            @endif

        @elseif($search && strlen($search) >= 2)
            <!-- No Results -->
            <div class="px-4 py-8 text-center">
                <x-icon name="magnifying-glass" class="w-12 h-12 text-slate-300 mx-auto mb-4" />
                <p class="text-sm text-slate-500 mb-1">No results found for "{{ $search }}"</p>
                <p class="text-xs text-slate-400">Try adjusting your search terms</p>
            </div>

        @elseif($search && strlen($search) < 2)
            <!-- Search too short -->
            <div class="px-4 py-6 text-center">
                <p class="text-sm text-slate-500">Type at least 2 characters to search</p>
            </div>
        @endif
    </div>
</div>