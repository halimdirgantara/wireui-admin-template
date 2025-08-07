<div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit User</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Update {{ $user->name }}'s information</p>
        </div>
        
        <div class="flex items-center space-x-3">
            <x-button href="{{ route('admin.users.show', $user) }}" secondary icon="eye">
                View User
            </x-button>
            <x-button href="{{ route('admin.users.index') }}" secondary icon="arrow-left">
                Back to Users
            </x-button>
        </div>
    </div>

    <!-- Edit User Form -->
    <x-card class="border-0 shadow-sm">
        <div class="p-6">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
                    </div>

                    <!-- Name -->
                    <div>
                        <x-input
                            wire:model="name"
                            label="Full Name"
                            placeholder="Enter user's full name"
                            icon="user"
                            required
                        />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-input
                            wire:model="email"
                            label="Email Address"
                            placeholder="Enter user's email"
                            icon="envelope"
                            type="email"
                            required
                        />
                    </div>

                    <!-- Avatar URL -->
                    <div class="md:col-span-2">
                        <x-input
                            wire:model="avatar"
                            label="Avatar URL (Optional)"
                            placeholder="https://example.com/avatar.jpg"
                            icon="photo"
                            hint="Leave empty to use auto-generated avatar"
                        />
                        
                        @if($avatar || $user->avatar)
                            <div class="mt-2">
                                <img class="h-16 w-16 rounded-full object-cover" 
                                     src="{{ $avatar ?: $user->avatar_url }}" 
                                     alt="Avatar Preview">
                            </div>
                        @endif
                    </div>

                    <!-- Password Section -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                            Change Password (Optional)
                        </h3>
                    </div>

                    <!-- Password -->
                    <div>
                        <x-password
                            wire:model="password"
                            label="New Password"
                            placeholder="Leave empty to keep current password"
                        />
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <x-password
                            wire:model="password_confirmation"
                            label="Confirm New Password"
                            placeholder="Confirm new password"
                        />
                    </div>

                    <!-- Settings Section -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                            Settings & Permissions
                        </h3>
                    </div>

                    <!-- Status -->
                    <div>
                        <x-select
                            wire:model="status"
                            label="Status"
                            placeholder="Select status"
                            required
                            :disabled="$user->id === auth()->id() && !auth()->user()->hasRole('Super Admin')"
                        >
                            <x-select.option label="Active" value="active" />
                            <x-select.option label="Inactive" value="inactive" />
                        </x-select>
                        
                        @if($user->id === auth()->id() && !auth()->user()->hasRole('Super Admin'))
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">You cannot change your own status</p>
                        @endif
                    </div>

                    <!-- Account Info -->
                    <div>
                        <div class="space-y-2">
                            <div class="text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">Created:</span>
                                <span class="text-gray-600 dark:text-gray-400">{{ $user->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">Last Updated:</span>
                                <span class="text-gray-600 dark:text-gray-400">{{ $user->updated_at->format('M d, Y H:i') }}</span>
                            </div>
                            @if($user->email_verified_at)
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Email Verified:</span>
                                    <span class="text-green-600 dark:text-green-400">{{ $user->email_verified_at->format('M d, Y') }}</span>
                                </div>
                            @else
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Email:</span>
                                    <span class="text-orange-600 dark:text-orange-400">Not Verified</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Roles -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Roles
                        </label>
                        
                        @if($user->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin'))
                            <p class="text-sm text-orange-600 dark:text-orange-400 mb-2">
                                You cannot modify Super Admin roles.
                            </p>
                        @elseif($user->id === auth()->id() && !auth()->user()->hasRole('Super Admin'))
                            <p class="text-sm text-orange-600 dark:text-orange-400 mb-2">
                                You cannot modify your own roles.
                            </p>
                        @endif
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <x-checkbox
                                    wire:model="selectedRoles"
                                    value="{{ $role->id }}"
                                    label="{{ $role->name }}"
                                    description="{{ $role->permissions->count() }} permissions"
                                    :disabled="($user->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin')) || ($user->id === auth()->id() && !auth()->user()->hasRole('Super Admin'))"
                                />
                            @endforeach
                        </div>
                        
                        @if(empty($roles->count()))
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">No roles available.</p>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <x-button wire:click="cancel" secondary>
                        Cancel
                    </x-button>
                    
                    <x-button type="submit" primary icon="check" spinner="save">
                        Update User
                    </x-button>
                </div>
            </form>
        </div>
    </x-card>
</div>
