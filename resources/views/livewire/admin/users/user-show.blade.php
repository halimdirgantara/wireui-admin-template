<div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        @section('title', 'User Details')
        @section('page-title', 'User Details')
        @section('page-description', "View {$user->name}'s information and activity")
        
        <div class="flex items-center space-x-3">
            @can('users.update')
            <x-button href="{{ route('admin.users.edit', $user) }}" primary icon="pencil">
                Edit User
            </x-button>
            @endcan
            <x-button href="{{ route('admin.users.index') }}" secondary icon="arrow-left">
                Back to Users
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Information Card -->
        <div class="lg:col-span-2">
            <x-card class="border-0 shadow-sm">
                <div class="p-6">
                    <div class="flex items-start space-x-6">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <img class="h-24 w-24 rounded-full object-cover" 
                                 src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=3b82f6&color=ffffff&size=96' }}" 
                                 alt="{{ $user->name }}">
                        </div>

                        <!-- Basic Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3 mb-4">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                                
                                <!-- Status Badge -->
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        Inactive
                                    </span>
                                @endif

                                @if($user->id === auth()->id())
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                        You
                                    </span>
                                @endif
                            </div>

                            <!-- Contact Info -->
                            <div class="space-y-3">
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                    <span>{{ $user->email }}</span>
                                    @if($user->email_verified_at)
                                        <svg class="w-4 h-4 ml-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>

                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Member since {{ $user->created_at->format('M d, Y') }}</span>
                                </div>

                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Last updated {{ $user->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Roles & Permissions Card -->
        <div>
            <x-card class="border-0 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Roles & Permissions</h3>
                    
                    @if($user->roles->count() > 0)
                        <div class="space-y-3">
                            @foreach($user->roles as $role)
                                @php
                                    $roleColors = [
                                        'Super Admin' => 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
                                        'Admin' => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
                                        'Editor' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
                                        'Viewer' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                    ];
                                    $colorClass = $roleColors[$role->name] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                @endphp
                                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                    <div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
                                            {{ $role->name }}
                                        </span>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $role->permissions->count() }} permissions
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No roles assigned</p>
                    @endif
                </div>
            </x-card>
        </div>

        <!-- Recent Activity Card -->
        <div class="lg:col-span-3">
            <x-card class="border-0 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Activity</h3>
                    
                    @if($recentActivities->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentActivities as $activity)
                                <div class="flex items-start space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                    <div class="flex-shrink-0">
                                        @if($activity->causer_id === $user->id)
                                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                        @else
                                            <div class="w-2 h-2 bg-gray-400 rounded-full mt-2"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $activity->description }}
                                        </p>
                                        <div class="flex items-center space-x-2 mt-1">
                                            @if($activity->causer)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    by {{ $activity->causer->name }}
                                                </span>
                                            @endif
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity</p>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</div>
