<div>
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        @section('title', 'Role Management')
        @section('page-title', 'Role Management')
        @section('page-description', 'Manage system roles and permissions')
        @section('breadcrumb')
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Roles</span>
                </div>
            </li>
        @endsection
        
        <div class="flex items-end gap-3">
            @can('roles.create')
            <x-button wire:click="openCreateModal" primary icon="plus" class="whitespace-nowrap">
                Add Role
            </x-button>
            @endcan

            @can('permissions.create')
            <x-button wire:click="openPermissionModal" secondary icon="shield-check" class="whitespace-nowrap">
                Add Permission
            </x-button>
            @endcan

            <!-- Bulk Actions -->
            <div x-show="$wire.selectedRoles.length > 0" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="flex items-center gap-2">
                
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <span x-text="$wire.selectedRoles.length"></span> selected
                </span>

                @can('roles.delete')
                <x-button xs negative icon="trash" 
                         x-on:confirm="{
                             title: 'Bulk Delete Roles',
                             description: 'Are you sure you want to delete the selected roles? This action cannot be undone.',
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <x-input 
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Search roles..."
                    class="w-full"
                />
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

    <!-- Roles Table -->
    <x-card class="border-0 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <!-- Select All Checkbox -->
                        <th class="px-6 py-3 text-left">
                            <x-checkbox wire:model.live="selectAll" />
                        </th>

                        <!-- Name -->
                        <th class="px-6 py-3 text-left">
                            <button 
                                wire:click="sortBy('name')"
                                class="group flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer"
                            >
                                <span>Role Name</span>
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

                        <!-- Users Count -->
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Users
                        </th>

                        <!-- Permissions Count -->
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Permissions
                        </th>

                        <!-- Created At -->
                        <th class="px-6 py-3 text-left">
                            <button 
                                wire:click="sortBy('created_at')"
                                class="group flex items-center space-x-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer"
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
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <!-- Select Checkbox -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-checkbox wire:model.live="selectedRoles" value="{{ $role->id }}" />
                            </td>

                            <!-- Role Name -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @php
                                            $roleIcons = [
                                                'Super Admin' => 'shield-check',
                                                'Admin' => 'user-group',
                                                'Editor' => 'pencil-square',
                                                'Viewer' => 'eye'
                                            ];
                                            $iconClass = $roleIcons[$role->name] ?? 'user-circle';
                                            
                                            $roleColors = [
                                                'Super Admin' => 'bg-purple-100 text-purple-600 dark:bg-purple-800 dark:text-purple-100',
                                                'Admin' => 'bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-100',
                                                'Editor' => 'bg-green-100 text-green-600 dark:bg-green-800 dark:text-green-100',
                                                'Viewer' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-200'
                                            ];
                                            $colorClass = $roleColors[$role->name] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-200';
                                        @endphp
                                        <div class="w-10 h-10 {{ $colorClass }} rounded-lg flex items-center justify-center">
                                            <x-icon name="{{ $iconClass }}" class="w-5 h-5" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $role->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Role ID: {{ $role->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Users Count -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-1">
                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $role->users_count }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">users</span>
                                </div>
                            </td>

                            <!-- Permissions Count -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-1">
                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $role->permissions_count }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">permissions</span>
                                </div>
                            </td>

                            <!-- Created At -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $role->created_at->format('M d, Y') }}</div>
                                <div class="text-xs">{{ $role->created_at->format('h:i A') }}</div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @can('roles.update')
                                        <x-mini-button primary icon="pencil" 
                                                        wire:click="openEditModal({{ $role->id }})"
                                                        label="Edit Role" />
                                    @endcan

                                    @can('roles.delete')
                                        @if($role->name !== 'Super Admin')
                                            <x-mini-button negative icon="trash"
                                                            x-on:confirm="{
                                                                title: 'Delete Role',
                                                                description: 'Are you sure you want to delete {{ $role->name }}? This action cannot be undone.',
                                                                icon: 'error',
                                                                accept: {
                                                                    label: 'Delete',
                                                                    method: 'deleteRole',
                                                                    params: {{ $role->id }}
                                                                }
                                                            }"
                                                            label="Delete Role" />
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <div class="text-gray-500 dark:text-gray-400">
                                        @if($search)
                                            <p class="text-sm font-medium">No roles match your search criteria</p>
                                            <p class="text-xs mt-1">Try adjusting your search terms</p>
                                        @else
                                            <p class="text-sm font-medium">No roles found</p>
                                            <p class="text-xs mt-1">Get started by creating your first role</p>
                                        @endif
                                    </div>
                                    @if($search)
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
        @if($roles->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $roles->links() }}
            </div>
        @endif
    </x-card>

    <!-- Create Role Modal -->
    <x-modal-card title="Create New Role" blur wire:model.defer="showCreateModal" max-width="4xl">
            <div class="space-y-6">
                    <!-- Role Name -->
                    <div>
                        <x-input 
                            wire:model="roleName"
                            label="Role Name"
                            placeholder="Enter role name"
                            required
                        />
                    </div>

                    <!-- Permissions -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                            Permissions
                        </label>
                        <div class="space-y-4">
                            @foreach($permissions as $category => $categoryPermissions)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3 capitalize">
                                        {{ $category }} ({{ $categoryPermissions->count() }} permissions)
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                        @foreach($categoryPermissions as $permission)
                                            <x-checkbox
                                                wire:model="selectedPermissions"
                                                value="{{ $permission->id }}"
                                                label="{{ $permission->name }}"
                                                class="text-xs"
                                            />
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
        
        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-button flat label="Cancel" x-on:click="close" />
                <x-button primary label="Create Role" wire:click="createRole" spinner="createRole" />
            </div>
        </x-slot>
    </x-modal-card>

    <!-- Edit Role Modal -->
    <x-modal-card title="Edit Role: {{ $roleName }}" blur wire:model.defer="showEditModal" max-width="4xl">
            <div class="space-y-6">
                    <!-- Role Name -->
                    <div>
                        <x-input 
                            wire:model="roleName"
                            label="Role Name"
                            placeholder="Enter role name"
                            required
                        />
                    </div>

                    <!-- Permissions -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                            Permissions
                        </label>
                        <div class="space-y-4">
                            @foreach($permissions as $category => $categoryPermissions)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3 capitalize">
                                        {{ $category }} ({{ $categoryPermissions->count() }} permissions)
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                        @foreach($categoryPermissions as $permission)
                                            <x-checkbox
                                                wire:model="selectedPermissions"
                                                value="{{ $permission->id }}"
                                                label="{{ $permission->name }}"
                                                class="text-xs"
                                            />
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
        
        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-button flat label="Cancel" x-on:click="close" />
                <x-button primary label="Update Role" wire:click="updateRole" spinner="updateRole" />
            </div>
        </x-slot>
    </x-modal-card>

    <!-- Create Permission Modal -->
    <x-modal-card title="Create New Permission" blur wire:model.defer="showPermissionModal" max-width="2xl">
            <div class="space-y-6">
                    <!-- Permission Category -->
                    <div>
                        <x-select
                            wire:model="permissionCategory"
                            label="Category"
                            placeholder="Select permission category"
                            required
                        >
                            <x-select.option label="Dashboard" value="dashboard" />
                            <x-select.option label="Users" value="users" />
                            <x-select.option label="Roles" value="roles" />
                            <x-select.option label="Permissions" value="permissions" />
                            <x-select.option label="Activity Logs" value="activity-logs" />
                            <x-select.option label="Settings" value="settings" />
                            <x-select.option label="Profile" value="profile" />
                            <x-select.option label="Reports" value="reports" />
                        </x-select>
                    </div>

                    <!-- Permission Name -->
                    <div>
                        <x-input 
                            wire:model="permissionName"
                            label="Permission Action"
                            placeholder="e.g., view, create, update, delete"
                            hint="The permission will be created as: {{ $permissionCategory }}.{{ $permissionName }}"
                            required
                        />
                    </div>

                    <!-- Preview -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Permission Preview:</p>
                        <p class="font-mono text-sm font-medium text-blue-600 dark:text-blue-400">
                            {{ $permissionCategory }}.{{ $permissionName ?: 'action' }}
                        </p>
                    </div>
                </div>
        
        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-button flat label="Cancel" x-on:click="close" />
                <x-button primary label="Create Permission" wire:click="createPermission" spinner="createPermission" />
            </div>
        </x-slot>
    </x-modal-card>
</div>
