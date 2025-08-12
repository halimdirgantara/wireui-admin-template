<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Blog Categories</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Organize your content with hierarchical categories
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            @can('categories.create')
                <x-button wire:click="create" primary icon="plus">
                    New Category
                </x-button>
            @endcan
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 mb-6 border border-gray-200/50 dark:border-gray-700/50">
        <div class="flex flex-col lg:flex-row gap-4">
            {{-- Search --}}
            <div class="flex-1">
                <x-input 
                    wire:model.live.debounce.500ms="search" 
                    placeholder="Search categories..." 
                    icon="magnifying-glass"
                    class="w-full"
                />
            </div>
            
            {{-- Filters --}}
            <div class="flex flex-wrap gap-2">
                <div class="flex items-center">
                    <x-checkbox wire:model.live="showInactive" id="show-inactive" />
                    <x-label for="show-inactive" class="ml-2">Show Inactive</x-label>
                </div>
                
                @if($search || $showInactive)
                    <x-button wire:click="$set('search', ''); $set('showInactive', false)" flat icon="x-mark" negative>
                        Clear
                    </x-button>
                @endif
            </div>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
        @if($categories->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('name')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                    Name
                                    @if($sortBy === 'name')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">Parent</th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('posts_count')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                    Posts
                                    @if($sortBy === 'posts_count')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('sort_order')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                    Order
                                    @if($sortBy === 'sort_order')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('created_at')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                    Created
                                    @if($sortBy === 'created_at')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
                        @foreach($categories as $category)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        {{-- Color indicator --}}
                                        <div class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $category->color }}"></div>
                                        
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                @if($category->icon)
                                                    <x-icon name="{{ $category->icon }}" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                                @endif
                                                <h3 class="font-medium text-gray-900 dark:text-white">
                                                    {{ $category->name }}
                                                </h3>
                                            </div>
                                            @if($category->description)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                                    {{ $category->description }}
                                                </p>
                                            @endif
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono">
                                                /{{ $category->slug }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($category->parent)
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            {{ $category->parent->name }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Root</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-900 dark:text-white">
                                        {{ number_format($category->posts_count) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-900 dark:text-white">
                                        {{ $category->sort_order ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full
                                        {{ $category->is_active 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                                            : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' 
                                        }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $category->created_at->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        @can('categories.update')
                                            <x-button 
                                                wire:click="toggleActive({{ $category->id }})" 
                                                size="sm"                                                 
                                                title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}"
                                            >
                                                <x-icon name="{{ $category->is_active ? 'eye-slash' : 'eye' }}" class="w-4 h-4" />
                                            </x-button>
                                        @endcan
                                        
                                        {{-- Dropdown Menu --}}
                                        <x-dropdown>
                                            <x-slot name="trigger">
                                                <x-button size="sm" flat>
                                                    <x-icon name="ellipsis-vertical" class="w-4 h-4" />
                                                </x-button>
                                            </x-slot>
                                            
                                            @can('categories.update')
                                                <x-dropdown.item wire:click="edit({{ $category->id }})" icon="pencil">
                                                    Edit
                                                </x-dropdown.item>
                                            @endcan
                                            
                                            @can('categories.reorder')
                                                <x-dropdown.item icon="arrows-up-down">
                                                    Reorder
                                                </x-dropdown.item>
                                            @endcan
                                            
                                            <x-dropdown.item separator />
                                            
                                            @can('categories.delete')
                                                <x-dropdown.item wire:click="delete({{ $category->id }})" icon="trash" class="text-red-600">
                                                    Delete
                                                </x-dropdown.item>
                                            @endcan
                                        </x-dropdown>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200/50 dark:border-gray-700/50">
                {{ $categories->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <x-icon name="rectangle-stack" class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No categories found</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    @if($search)
                        Try adjusting your search criteria.
                    @else
                        Get started by creating your first category.
                    @endif
                </p>
                @can('categories.create')
                    <x-button wire:click="create" primary icon="plus">
                        Create First Category
                    </x-button>
                @endcan
            </div>
        @endif
    </div>

    {{-- Category Modal --}}
    <x-modal wire:model="showModal" max-width="2xl">
        <x-card title="{{ $isEditing ? 'Edit Category' : 'Create New Category' }}">
            <form wire:submit="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Basic Information --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Basic Information</h3>
                        
                        <div>
                            <x-input
                                wire:model.live.debounce.300ms="name"
                                label="Category Name"
                                placeholder="Enter category name..."
                                required
                            />
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <x-label for="slug" value="URL Slug" />
                                <x-button 
                                    type="button"
                                    wire:click="generateSlug" 
                                    flat 
                                    size="sm"
                                >
                                    <x-icon name="arrow-path" class="w-3 h-3 mr-1" />
                                    Generate
                                </x-button>
                            </div>
                            <x-input
                                wire:model="slug"
                                id="slug"
                                placeholder="url-friendly-slug"
                                class="font-mono text-sm"
                                required
                            />
                        </div>

                        <div>
                            <x-textarea
                                wire:model.live.debounce.300ms="description"
                                label="Description"
                                placeholder="Brief description of this category..."
                                rows="3"
                            />
                        </div>

                        <div>
                            <x-select
                                wire:model="parent_id"
                                label="Parent Category"
                                placeholder="Select parent category (optional)"
                                :option-label="'label'"
                                :option-value="'value'"
                                :options="$parentCategories->map(fn($cat) => ['label' => $cat->name, 'value' => $cat->id])"
                            />
                        </div>
                    </div>

                    {{-- Appearance & Settings --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Appearance & Settings</h3>
                        
                        <div>
                            <x-label for="color" value="Color" />
                            <div class="mt-2 flex items-center gap-3">
                                <input 
                                    type="color" 
                                    wire:model="color" 
                                    id="color"
                                    class="w-12 h-10 rounded-lg border border-gray-300 dark:border-gray-700 cursor-pointer"
                                >
                                <x-input
                                    wire:model="color"
                                    placeholder="#3b82f6"
                                    class="font-mono text-sm flex-1"
                                />
                            </div>
                        </div>

                        <div>
                            <x-input
                                wire:model="icon"
                                label="Icon (Heroicons name)"
                                placeholder="rectangle-stack"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Visit <a href="https://heroicons.com" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">heroicons.com</a> for icon names
                            </p>
                        </div>

                        <div>
                            <x-input
                                wire:model="image"
                                label="Category Image URL"
                                placeholder="https://example.com/image.jpg"
                                type="url"
                            />
                        </div>

                        <div>
                            <x-input
                                wire:model="sort_order"
                                label="Sort Order"
                                placeholder="Auto-assigned if empty"
                                type="number"
                                min="0"
                            />
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center">
                                <x-checkbox wire:model="is_active" id="is_active" />
                                <x-label for="is_active" class="ml-2">Active</x-label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEO Section --}}
                <div class="border-t border-gray-200/50 dark:border-gray-700/50 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">SEO Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input
                                wire:model="seo_title"
                                label="SEO Title"
                                placeholder="Optimized title for search engines..."
                                maxlength="60"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ strlen($seo_title ?? '') }}/60 characters
                            </p>
                        </div>

                        <div>
                            <x-input
                                wire:model="meta_keywords"
                                label="Meta Keywords"
                                placeholder="keyword1, keyword2, keyword3..."
                            />
                        </div>

                        <div class="md:col-span-2">
                            <x-textarea
                                wire:model="seo_description"
                                label="SEO Description"
                                placeholder="Brief description for search engine results..."
                                rows="3"
                                maxlength="160"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ strlen($seo_description ?? '') }}/160 characters
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Modal Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                    <x-button wire:click="closeModal" outline secondary>
                        Cancel
                    </x-button>
                    <x-button type="submit" primary>
                        {{ $isEditing ? 'Update Category' : 'Create Category' }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </x-modal>
</div>
