<div>
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <!-- Page Header -->
        @section('title', 'Create New User')
        @section('page-title', 'Create New User')
        @section('page-description', 'Add a new user to the system')
        
        <div class="flex items-end gap-3">
            @can('users.create')
            <x-button href="{{ route('admin.users.create') }}" primary icon="plus" class="whitespace-nowrap">
                Add User
            </x-button>
            @endcan

            <!-- Bulk Actions -->
            <div x-show="$wire.selectedUsers.length > 0" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="flex items-center gap-2">
                
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <span x-text="$wire.selectedUsers.length"></span> selected
                </span>

                @can('users.update')
                <x-button xs secondary icon="check-circle" wire:click="bulkActivate" class="whitespace-nowrap">
                    Activate
                </x-button>
                
                <x-button xs secondary icon="x-circle" wire:click="bulkDeactivate" class="whitespace-nowrap">
                    Deactivate  
                </x-button>
                @endcan

                @can('users.delete')
                <x-button xs negative icon="trash" 
                         x-on:confirm="{
                             title: 'Bulk Delete Users',
                             description: 'Are you sure you want to delete the selected users? This action cannot be undone.',
                             icon: 'error',
                             accept: {
                                 label: 'Delete All',
                                 method: 'bulkDelete'
                             }
                         }"
                         class="whitespace-nowrap">
                    Delete
                </x-button>
                @endcan
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <x-card class="mb-6 border-0 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <x-input 
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Search users..."
                    class="w-full"
                />
            </div>

            <!-- Status Filter -->
            <div>
                <x-select 
                    wire:model.live="statusFilter"
                    placeholder="All Status"
                    class="w-full"
                >
                    <x-select.option label="All Status" value="all" />
                    <x-select.option label="Active" value="active" />
                    <x-select.option label="Inactive" value="inactive" />
                </x-select>
            </div>

            <!-- Role Filter -->
            <div>
                <x-select 
                    wire:model.live="roleFilter"
                    placeholder="All Roles"
                    class="w-full"
                >
                    <x-select.option label="All Roles" value="all" />
                    @foreach($roles as $role)
                        <x-select.option label="{{ $role->name }}" value="{{ $role->name }}" />
                    @endforeach
                </x-select>
            </div>

            <!-- Per Page -->
            <div>
                <x-select 
                    wire:model.live="perPage"
                    class="w-full"
                >
                    <x-select.option label="10 per page" value="10" />
                    <x-select.option label="25 per page" value="25" />
                    <x-select.option label="50 per page" value="50" />
                    <x-select.option label="100 per page" value="100" />
                </x-select>
            </div>

            <!-- Reset Filters -->
            <div class="flex items-end">
                <x-button 
                    wire:click="resetFilters"
                    secondary 
                    icon="arrow-path"
                    class="w-full"
                >
                    Reset
                </x-button>
            </div>
        </div>
    </x-card>

    <!-- Users Table -->
    <x-card class="border-0 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <!-- Select All Checkbox -->
                        <th class="px-6 py-3 text-left">
                            <x-checkbox wire:model.live="selectAll" />
                        </th>

                        <!-- Avatar & Name -->
                        <th class="px-6 py-3 text-left">
                            <button 
                                wire:click="sortBy('name')"
                                class="group flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                            >
                                <span>User</span>
                                @if($sortField === 'name')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                    </svg>
                                @endif
                            </button>
                        </th>

                        <!-- Email -->
                        <th class="px-6 py-3 text-left">
                            <button 
                                wire:click="sortBy('email')"
                                class="group flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                            >
                                <span>Email</span>
                                @if($sortField === 'email')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                    </svg>
                                @endif
                            </button>
                        </th>

                        <!-- Roles -->
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Roles
                        </th>

                        <!-- Status -->
                        <th class="px-6 py-3 text-left">
                            <button 
                                wire:click="sortBy('status')"
                                class="group flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                            >
                                <span>Status</span>
                                @if($sortField === 'status')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                    </svg>
                                @endif
                            </button>
                        </th>

                        <!-- Created At -->
                        <th class="px-6 py-3 text-left">
                            <button 
                                wire:click="sortBy('created_at')"
                                class="group flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                            >
                                <span>Created</span>
                                @if($sortField === 'created_at')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                    </svg>
                                @endif
                            </button>
                        </th>

                        <!-- Actions -->
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <!-- Select Checkbox -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-checkbox wire:model.live="selectedUsers" value="{{ $user->id }}" />
                            </td>

                            <!-- Avatar & Name -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=3b82f6&color=ffffff' }}" 
                                             alt="{{ $user->name }}">
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $user->name }}
                                        </div>
                                        @if($user->id === auth()->id())
                                            <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">
                                                (You)
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</div>
                                @if($user->email_verified_at)
                                    <div class="text-xs text-green-600 dark:text-green-400 flex items-center mt-1">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Verified
                                    </div>
                                @else
                                    <div class="text-xs text-orange-600 dark:text-orange-400 flex items-center mt-1">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Unverified
                                    </div>
                                @endif
                            </td>

                            <!-- Roles -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        @php
                                            $roleColors = [
                                                'Super Admin' => 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
                                                'Admin' => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
                                                'Editor' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
                                                'Viewer' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                            ];
                                            $colorClass = $roleColors[$role->name] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                            No Role
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Created At -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $user->created_at->format('M d, Y') }}</div>
                                <div class="text-xs">{{ $user->created_at->format('h:i A') }}</div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @can('users.view')
                                        <x-mini-button info icon="eye" 
                                                        href="{{ route('admin.users.show', $user) }}"
                                                        label="" />
                                    @endcan

                                    @can('users.update')
                                        <x-mini-button icon="pencil" 
                                                        href="{{ route('admin.users.edit', $user) }}"
                                                        label="" />
                                        
                                        @if($user->id !== auth()->id())
                                            <x-mini-button 
                                                            class="{{ $user->status === 'active' ? 'text-orange-600 hover:text-orange-800 dark:text-orange-400' : 'text-green-600 hover:text-green-800 dark:text-green-400' }}"
                                                            wire:click="toggleUserStatus({{ $user->id }})"
                                                            icon="{{ $user->status === 'active' ? 'pause' : 'play' }}"
                                                            label="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }} User" />
                                        @endif
                                    @endcan

                                    @can('users.delete')
                                        @if($user->id !== auth()->id() && (!$user->hasRole('Super Admin') || auth()->user()->hasRole('Super Admin')))
                                            <x-mini-button red icon="trash"
                                                            x-on:confirm="{
                                                                title: 'Delete User',
                                                                description: 'Are you sure you want to delete {{ $user->name }}? This action cannot be undone.',
                                                                icon: 'error',
                                                                accept: {
                                                                    label: 'Delete',
                                                                    method: 'deleteUser',
                                                                    params: {{ $user->id }}
                                                                }
                                                            }"
                                                            label="" />
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    <div class="text-gray-500 dark:text-gray-400">
                                        @if($search || $statusFilter !== 'all' || $roleFilter !== 'all')
                                            <p class="text-sm font-medium">No users match your search criteria</p>
                                            <p class="text-xs mt-1">Try adjusting your filters or search terms</p>
                                        @else
                                            <p class="text-sm font-medium">No users found</p>
                                            <p class="text-xs mt-1">Get started by creating your first user</p>
                                        @endif
                                    </div>
                                    @if($search || $statusFilter !== 'all' || $roleFilter !== 'all')
                                        <x-button wire:click="resetFilters" secondary sm>
                                            Clear Filters
                                        </x-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $users->links() }}
            </div>
        @endif
    </x-card>
</div>
