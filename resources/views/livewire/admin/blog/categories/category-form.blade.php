<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $isEditing ? 'Edit Category' : 'Create Category' }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $isEditing ? 'Update category information and settings' : 'Create a new blog category' }}
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <x-button href="{{ route('admin.blog.categories.index') }}" outline icon="arrow-left">
                Back to Categories
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
                                label="Category Name *"
                                placeholder="Enter category name..."
                                class="w-full"
                            />
                        </div>
                        
                        {{-- Slug --}}
                        <div>
                            <x-input 
                                wire:model="slug"
                                label="URL Slug *"
                                placeholder="category-url-slug"
                                class="w-full"
                                right-icon="link"
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
                                placeholder="Brief description of this category..."
                                rows="3"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>

                {{-- SEO Settings --}}
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 border border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">SEO Settings</h3>
                    
                    <div class="space-y-4">
                        {{-- SEO Title --}}
                        <div>
                            <x-input 
                                wire:model="seo_title"
                                label="SEO Title"
                                placeholder="SEO-friendly title (max 60 chars)"
                                class="w-full"
                                hint="Leave empty to use category name"
                            />
                        </div>
                        
                        {{-- SEO Description --}}
                        <div>
                            <x-textarea 
                                wire:model="seo_description"
                                label="SEO Description"
                                placeholder="SEO meta description (max 160 chars)..."
                                rows="2"
                                class="w-full"
                                hint="Leave empty to use category description"
                            />
                        </div>
                        
                        {{-- Meta Keywords --}}
                        <div>
                            <x-input 
                                wire:model="meta_keywords"
                                label="Meta Keywords"
                                placeholder="keyword1, keyword2, keyword3"
                                class="w-full"
                                hint="Comma-separated keywords"
                            />
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
                                description="Make this category visible on the site"
                            />
                        </div>
                        
                        {{-- Sort Order --}}
                        <div>
                            <x-number 
                                wire:model="sort_order"
                                label="Sort Order"
                                placeholder="0"
                                min="0"
                                class="w-full"
                                hint="Lower numbers appear first"
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
                                    {{ $isEditing ? 'Update Category' : 'Create Category' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    {{ $isEditing ? 'Updating...' : 'Creating...' }}
                                </span>
                            </x-button>
                        </div>
                    </div>
                </div>

                {{-- Hierarchy --}}
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 border border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hierarchy</h3>
                    
                    <div class="space-y-4">
                        {{-- Parent Category --}}
                        <div>
                            <x-select 
                                wire:model="parent_id"
                                label="Parent Category"
                                placeholder="Select parent category..."
                                class="w-full"
                            >
                                <x-select.option value="">No Parent (Root Category)</x-select.option>
                                @foreach($parentCategories as $parent)
                                    <x-select.option value="{{ $parent->id }}">
                                        {{ $parent->name }}
                                    </x-select.option>
                                @endforeach
                            </x-select>
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
                                label="Category Color"
                                hint="Used for badges and visual identification"
                            />
                        </div>
                        
                        {{-- Icon --}}
                        <div>
                            <x-input 
                                wire:model="icon"
                                label="Icon Class"
                                placeholder="heroicon-o-folder"
                                class="w-full"
                                hint="CSS class for icon (optional)"
                            />
                        </div>
                        
                        {{-- Image URL --}}
                        <div>
                            <x-input 
                                wire:model="image"
                                label="Image URL"
                                placeholder="https://example.com/image.jpg"
                                class="w-full"
                                hint="Featured image for category (optional)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>