<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Blog Tags</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Manage content tags and organize your posts with labels
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            @can('tags.create')
                <x-button wire:click="create" primary icon="plus">
                    New Tag
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
                    placeholder="Search tags..." 
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

    {{-- Bulk Actions --}}
    @if(count($selectedTags) > 0)
        <div class="bg-blue-50 dark:bg-blue-900/20 backdrop-blur-sm rounded-xl p-4 mb-6 border border-blue-200/50 dark:border-blue-700/50">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    {{ count($selectedTags) }} tag{{ count($selectedTags) === 1 ? '' : 's' }} selected
                </span>
                
                <div class="flex flex-wrap gap-2">
                    @can('tags.update')
                        <x-button wire:click="bulkToggleActive(true)" outline positive size="sm">
                            Activate
                        </x-button>
                        <x-button wire:click="bulkToggleActive(false)" outline secondary size="sm">
                            Deactivate
                        </x-button>
                    @endcan
                    
                    @can('tags.delete')
                        <x-button wire:click="bulkDelete" outline negative size="sm">
                            Delete
                        </x-button>
                    @endcan
                </div>
            </div>
        </div>
    @endif

    {{-- Tags Grid/Table --}}
    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
        @if($tags->count() > 0)
            {{-- Table View --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/80">
                        <tr>
                            <th class="w-10 px-4 py-3">
                                <x-checkbox wire:model.live="selectAll" />
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('name')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white cursor-pointer">
                                    Name
                                    @if($sortBy === 'name')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('posts_count')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white cursor-pointer">
                                    Posts
                                    @if($sortBy === 'posts_count')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('created_at')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white cursor-pointer">
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
                        @foreach($tags as $tag)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-3">
                                    <x-checkbox wire:model.live="selectedTags" value="{{ $tag->id }}" />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full {{ $tag->color_class }}">
                                            {{ $tag->name }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            @if($tag->description)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                                    {{ $tag->description }}
                                                </p>
                                            @endif
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono">
                                                /{{ $tag->slug }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ number_format($tag->posts_count ?? 0) }}
                                        </span>
                                        @if($tag->published_posts_count ?? 0 < $tag->posts_count ?? 0)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                ({{ number_format($tag->published_posts_count ?? 0) }} published)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full
                                        {{ $tag->is_active 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                                            : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' 
                                        }}">
                                        {{ $tag->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $tag->created_at->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        @can('tags.update')
                                            <x-button 
                                                wire:click="toggleActive({{ $tag->id }})" 
                                                size="sm"
                                                title="{{ $tag->is_active ? 'Deactivate' : 'Activate' }}"
                                            >
                                                <x-icon name="{{ $tag->is_active ? 'eye-slash' : 'eye' }}" class="w-4 h-4" />
                                            </x-button>
                                        @endcan
                                        
                                        {{-- Dropdown Menu --}}
                                        <x-dropdown>
                                            <x-slot name="trigger">
                                                <x-button size="sm" flat>
                                                    <x-icon name="ellipsis-vertical" class="w-4 h-4" />
                                                </x-button>
                                            </x-slot>
                                            
                                            @can('tags.update')
                                                <x-dropdown.item wire:click="edit({{ $tag->id }})" icon="pencil">
                                                    Edit
                                                </x-dropdown.item>
                                            @endcan
                                            
                                            @can('tags.merge')
                                                <x-dropdown.item wire:click="showMergeDialog({{ $tag->id }})" icon="arrows-pointing-in">
                                                    Merge Into...
                                                </x-dropdown.item>
                                            @endcan
                                            
                                            <x-dropdown.item separator />
                                            
                                            @can('tags.delete')
                                                <x-dropdown.item wire:click="delete({{ $tag->id }})" icon="trash" class="text-red-600">
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
                {{ $tags->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <x-icon name="tag" class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No tags found</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    @if($search)
                        Try adjusting your search criteria.
                    @else
                        Tags help organize and categorize your content.
                    @endif
                </p>
                @can('tags.create')
                    <x-button wire:click="create" primary icon="plus">
                        Create First Tag
                    </x-button>
                @endcan
            </div>
        @endif
    </div>

    {{-- Tag Modal --}}
    <x-modal wire:model="showModal" max-width="lg">
        <x-card title="{{ $isEditing ? 'Edit Tag' : 'Create New Tag' }}">
            <form wire:submit="save" class="space-y-6">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <x-input
                            wire:model.live.debounce.300ms="name"
                            label="Tag Name"
                            placeholder="Enter tag name..."
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
                            wire:model="description"
                            label="Description"
                            placeholder="Brief description of this tag..."
                            rows="3"
                        />
                    </div>

                    <div>
                        <x-label for="color" value="Tag Color" />
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
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Preview:</span>
                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full" 
                                      style="background-color: {{ $color }}20; color: {{ $color }};">
                                    {{ $name ?: 'Sample Tag' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <x-checkbox wire:model="is_active" id="is_active" />
                        <x-label for="is_active" class="ml-2">Active</x-label>
                    </div>
                </div>

                {{-- Modal Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                    <x-button wire:click="closeModal" outline secondary>
                        Cancel
                    </x-button>
                    <x-button type="submit" primary>
                        {{ $isEditing ? 'Update Tag' : 'Create Tag' }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </x-modal>

    {{-- Tag Merge Modal --}}
    <x-modal wire:model="showMergeModal" max-width="md">
        <x-card title="Merge Tag">
            <form wire:submit="mergeTags" class="space-y-6">
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200/50 dark:border-amber-700/50 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <x-icon name="exclamation-triangle" class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                        <div>
                            <h3 class="font-medium text-amber-800 dark:text-amber-200">Merge Warning</h3>
                            <p class="text-sm text-amber-600 dark:text-amber-300 mt-1">
                                This action will move all posts from the source tag to the target tag and delete the source tag. This cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <x-label value="Source Tag (will be deleted)" />
                        @if($sourceTagId)
                            @php $sourceTag = $allTags->firstWhere('id', $sourceTagId) @endphp
                            <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full {{ $sourceTag->color_class ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $sourceTag->name ?? 'Unknown' }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
                                    ({{ $sourceTag->posts_count ?? 0 }} posts)
                                </span>
                            </div>
                        @endif
                    </div>

                    <div>
                        <x-select
                            wire:model="targetTagId"
                            label="Target Tag (posts will be moved here)"
                            placeholder="Select target tag..."
                            :option-label="'label'"
                            :option-value="'value'"
                            :options="$allTags->where('id', '!=', $sourceTagId)->map(fn($tag) => ['label' => $tag->name . ' (' . ($tag->posts_count ?? 0) . ' posts)', 'value' => $tag->id])"
                            required
                        />
                    </div>
                </div>

                {{-- Modal Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                    <x-button wire:click="closeMergeModal" outline secondary>
                        Cancel
                    </x-button>
                    <x-button type="submit" negative>
                        Merge Tags
                    </x-button>
                </div>
            </form>
        </x-card>
    </x-modal>
</div>
