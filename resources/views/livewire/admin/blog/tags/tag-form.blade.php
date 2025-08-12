<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $isEditing ? 'Edit Tag' : 'Create Tag' }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $isEditing ? 'Update tag information and settings' : 'Create a new blog tag' }}
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <x-button href="{{ route('admin.blog.tags.index') }}" outline icon="arrow-left">
                Back to Tags
            </x-button>
        </div>
    </div>

    {{-- Main Form --}}
    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Basic Information --}}
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 border border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>
                    
                    <div class="space-y-4">
                        {{-- Name --}}
                        <div>
                            <x-input 
                                wire:model.live="name"
                                label="Tag Name *"
                                placeholder="Enter tag name..."
                                class="w-full"
                                maxlength="100"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Maximum 100 characters
                            </p>
                        </div>
                        
                        {{-- Slug --}}
                        <div>
                            <x-input 
                                wire:model="slug"
                                label="URL Slug *"
                                placeholder="tag-url-slug"
                                class="w-full"
                                right-icon="link"
                                maxlength="100"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Used in URLs. Auto-generated from name if left empty.
                            </p>
                        </div>
                        
                        {{-- Description --}}
                        <div>
                            <x-textarea 
                                wire:model="description"
                                label="Description"
                                placeholder="Brief description of this tag..."
                                rows="3"
                                class="w-full"
                                maxlength="500"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Maximum 500 characters
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Publishing Options --}}
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 border border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Publishing</h3>
                    
                    <div class="space-y-4">
                        {{-- Active Status --}}
                        <div>
                            <x-checkbox 
                                wire:model="is_active"
                                label="Active"
                                description="Make this tag visible on the site"
                            />
                        </div>

                        {{-- Save Actions --}}
                        <div class="pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                            <x-button 
                                type="submit" 
                                primary 
                                class="w-full" 
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="save">
                                    {{ $isEditing ? 'Update Tag' : 'Create Tag' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    {{ $isEditing ? 'Updating...' : 'Creating...' }}
                                </span>
                            </x-button>
                        </div>
                    </div>
                </div>

                {{-- Appearance --}}
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 border border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Appearance</h3>
                    
                    <div class="space-y-4">
                        {{-- Color --}}
                        <div>
                            <x-color-picker 
                                wire:model="color"
                                label="Tag Color"
                                hint="Used for badges and visual identification"
                            />
                        </div>
                        
                        {{-- Color Preview --}}
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Preview:</span>
                            <span 
                                class="px-2 py-1 text-xs font-medium rounded text-white"
                                style="background-color: {{ $color }}"
                            >
                                {{ $name ?: 'Sample Tag' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 border border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    
                    <div class="space-y-3">
                        <x-button 
                            wire:click="generateSlug"
                            outline 
                            icon="arrow-path"
                            class="w-full"
                            :disabled="!$name"
                        >
                            Regenerate Slug
                        </x-button>
                    </div>
                </div>

                {{-- Help --}}
                <div class="bg-blue-50/50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200/50 dark:border-blue-700/50">
                    <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Tag Best Practices</h4>
                    <ul class="text-xs text-blue-700 dark:text-blue-200 space-y-1">
                        <li>• Keep tag names short and descriptive</li>
                        <li>• Use lowercase for consistency</li>
                        <li>• Avoid special characters in names</li>
                        <li>• Choose colors that stand out</li>
                        <li>• Write helpful descriptions for editors</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>