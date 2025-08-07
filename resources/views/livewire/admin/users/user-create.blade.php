<div>
    <!-- Page Header -->
    @section('title', 'Create New User')
    @section('page-title', 'Create New User')
    @section('page-description', 'Add a new user to the system')

    <!-- Create User Form -->
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
                        <x-input wire:model="name" label="Full Name" placeholder="Enter user's full name" icon="user"
                            required />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-input wire:model="email" label="Email Address" placeholder="Enter user's email"
                            icon="envelope" type="email" required />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-password wire:model="password" label="Password" placeholder="Enter password" required />
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <x-password wire:model="password_confirmation" label="Confirm Password"
                            placeholder="Confirm password" required />
                    </div>

                    <!-- Avatar URL -->
                    <div class="md:col-span-2">
                        <x-input wire:model="avatar" label="Avatar URL (Optional)"
                            placeholder="https://example.com/avatar.jpg" icon="photo"
                            hint="Leave empty to use auto-generated avatar" />
                    </div>

                    <!-- Status -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-gray-900">Status</label>
                            <p class="text-sm text-gray-500">User Active Status</p>
                        </div>
                        <x-toggle wire:model="status" id="status" name="status" positive xl />
                    </div>

                    <!-- Roles -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Roles
                        </label>
                        <div class="space-y-2">
                            @foreach ($roles as $role)
                                <x-checkbox wire:model="selectedRoles" id="{{ $role->name }}"
                                    value="{{ $role->id }}" label="{{ $role->name }}"
                                    description="{{ $role->permissions->count() }} permissions" />
                            @endforeach
                        </div>
                        @if (empty($roles->count()))
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">No roles available.</p>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div
                    class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <x-button wire:click="cancel" secondary>
                        Cancel
                    </x-button>

                    <x-button type="submit" primary icon="check" spinner="save">
                        Create User
                    </x-button>
                </div>
            </form>
        </div>
    </x-card>
</div>
