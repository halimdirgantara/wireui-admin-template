<div class="relative" 
     x-data="{ 
        showDropdown: @entangle('showDropdown'),
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
     x-on:start-notification-refresh.window="startAutoRefresh($event.detail.interval)"
     x-on:stop-notification-refresh.window="stopAutoRefresh()"
     x-on:click.away="showDropdown = false"
     x-init="$watch('autoRefresh', value => {
        if (value) {
            startAutoRefresh({{ $refreshInterval * 1000 }});
        } else {
            stopAutoRefresh();
        }
     })">
    
    <!-- Notification Button -->
    <button @click="$wire.toggleDropdown()" 
            class="relative p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 rounded-lg flat-button transition-colors">
        <x-icon name="bell" class="w-5 h-5" />
        
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Notification Dropdown -->
    <div x-show="showDropdown" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 transform scale-95 translate-y-2"
         class="absolute right-0 z-50 mt-2 w-96 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 rounded-lg shadow-xl max-h-96 overflow-hidden">
        
        <!-- Header -->
        <div class="p-4 border-b border-gray-200/50 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Notifications</h3>
                <div class="flex items-center space-x-2">
                    @if($unreadCount > 0)
                        <button wire:click="markAllAsRead" 
                                class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                            Mark all read
                        </button>
                    @endif
                    
                    <button wire:click="refresh" 
                            class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                        <x-icon name="arrow-path" class="w-4 h-4" />
                    </button>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="flex space-x-1 mt-3 bg-gray-100/50 dark:bg-gray-700/50 rounded-lg p-1">
                @foreach($availableFilters as $filterKey => $filterLabel)
                    <button wire:click="$set('filter', '{{ $filterKey }}')" 
                            class="flex-1 text-xs font-medium py-1.5 px-3 rounded-md transition-all duration-200
                                   @if($filter === $filterKey) 
                                       bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm
                                   @else 
                                       text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200
                                   @endif">
                        {{ $filterLabel }}
                        @if($filterKey === 'unread' && $unreadCount > 0)
                            <span class="ml-1 px-1.5 py-0.5 bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 rounded-full text-xs">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-80 overflow-y-auto">
            @forelse($notifications as $notification)
                <div class="p-4 border-b border-gray-200/30 dark:border-gray-700/30 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors 
                           @if(is_null($notification->read_at)) bg-blue-50/30 dark:bg-blue-900/10 @endif">
                    <div class="flex items-start space-x-3">
                        <!-- Notification Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-{{ $this->getNotificationColor($notification) }}-100 dark:bg-{{ $this->getNotificationColor($notification) }}-900/50 flex items-center justify-center">
                                <x-icon name="{{ $this->getNotificationIcon($notification) }}" 
                                        class="w-4 h-4 text-{{ $this->getNotificationColor($notification) }}-600 dark:text-{{ $this->getNotificationColor($notification) }}-400" />
                            </div>
                        </div>
                        
                        <!-- Notification Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $notification->data['title'] ?? 'Notification' }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                        {{ $notification->data['message'] ?? 'No message available' }}
                                    </p>
                                    <div class="flex items-center space-x-2 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        <time>{{ $notification->created_at->diffForHumans() }}</time>
                                        @if(is_null($notification->read_at))
                                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                            <span>New</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex items-center space-x-1 ml-2" x-data="{ showActions: false }">
                                    <button @click="showActions = !showActions" 
                                            class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                        <x-icon name="ellipsis-vertical" class="w-4 h-4" />
                                    </button>
                                    
                                    <div x-show="showActions" 
                                         @click.away="showActions = false"
                                         x-transition
                                         class="absolute right-0 top-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-10 py-1 min-w-32">
                                        
                                        @if(isset($notification->data['url']) || $this->getNotificationUrl($notification) !== route('admin.dashboard'))
                                            <a href="{{ $this->getNotificationUrl($notification) }}" 
                                               @click="$wire.closeDropdown()"
                                               class="block px-3 py-1 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                                <x-icon name="arrow-top-right-on-square" class="w-3 h-3 inline mr-1" />
                                                View
                                            </a>
                                        @endif
                                        
                                        @if(is_null($notification->read_at))
                                            <button wire:click="markAsRead('{{ $notification->id }}')" 
                                                    @click="showActions = false"
                                                    class="block w-full text-left px-3 py-1 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                                <x-icon name="check" class="w-3 h-3 inline mr-1" />
                                                Mark as read
                                            </button>
                                        @else
                                            <button wire:click="markAsUnread('{{ $notification->id }}')" 
                                                    @click="showActions = false"
                                                    class="block w-full text-left px-3 py-1 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                                <x-icon name="minus" class="w-3 h-3 inline mr-1" />
                                                Mark as unread
                                            </button>
                                        @endif
                                        
                                        <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                                        
                                        <button wire:click="deleteNotification('{{ $notification->id }}')" 
                                                @click="showActions = false"
                                                class="block w-full text-left px-3 py-1 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-icon name="trash" class="w-3 h-3 inline mr-1" />
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="p-8 text-center">
                    <x-icon name="bell-slash" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                        @if($filter === 'unread')
                            No unread notifications
                        @elseif($filter === 'read')
                            No read notifications
                        @else
                            No notifications
                        @endif
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @if($filter === 'unread')
                            You're all caught up! New notifications will appear here.
                        @elseif($filter === 'read')
                            Read notifications will appear here.
                        @else
                            Notifications will appear here when you receive them.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if($notifications->hasPages() || $notifications->count() > 0)
            <div class="p-3 border-t border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        @if($notifications->hasPages())
                            <div class="flex items-center space-x-1">
                                @if($notifications->onFirstPage())
                                    <span class="w-6 h-6 flex items-center justify-center text-gray-400">
                                        <x-icon name="chevron-left" class="w-3 h-3" />
                                    </span>
                                @else
                                    <button wire:click="previousPage" 
                                            class="w-6 h-6 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                                        <x-icon name="chevron-left" class="w-3 h-3" />
                                    </button>
                                @endif
                                
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $notifications->currentPage() }} / {{ $notifications->lastPage() }}
                                </span>
                                
                                @if($notifications->hasMorePages())
                                    <button wire:click="nextPage" 
                                            class="w-6 h-6 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                                        <x-icon name="chevron-right" class="w-3 h-3" />
                                    </button>
                                @else
                                    <span class="w-6 h-6 flex items-center justify-center text-gray-400">
                                        <x-icon name="chevron-right" class="w-3 h-3" />
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-2 text-xs">
                        @if($autoRefresh)
                            <span class="inline-flex items-center text-green-600 dark:text-green-400">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse mr-1"></span>
                                Auto-refresh
                            </span>
                        @endif
                        
                        @if($notifications->where('read_at', '!=', null)->count() > 0)
                            <button wire:click="deleteAllRead" 
                                    class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                                    onclick="return confirm('Are you sure you want to delete all read notifications?')">
                                Clear read
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>