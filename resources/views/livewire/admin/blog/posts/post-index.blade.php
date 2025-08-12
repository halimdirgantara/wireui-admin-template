<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Blog Posts</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Manage your blog content, categories, and publishing schedule
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            @can('posts.create')
                <x-button href="{{ route('admin.blog.posts.create') }}" primary icon="plus">
                    New Post
                </x-button>
            @endcan
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 mb-6 border border-gray-200/50 dark:border-gray-700/50">
        <div class="flex flex-col lg:flex-row gap-4 mb-4">
            {{-- Search --}}
            <div class="flex-1">
                <x-input 
                    wire:model.live.debounce.500ms="search" 
                    placeholder="Search posts, authors, content..." 
                    icon="magnifying-glass"
                    class="w-full"
                />
            </div>
            
            {{-- Quick Filters --}}
            <div class="flex flex-wrap gap-2">
                <x-button 
                    wire:click="toggleFilters" 
                    outline 
                    icon="{{ $showFilters ? 'eye-slash' : 'funnel' }}"
                    class="whitespace-nowrap"
                >
                    {{ $showFilters ? 'Hide' : 'Show' }} Filters
                </x-button>
                
                @if($search || $statusFilter || $categoryFilter || $authorFilter || $dateFrom || $dateTo)
                    <x-button wire:click="clearFilters" flat icon="x-mark" negative>
                        Clear
                    </x-button>
                @endif
            </div>
        </div>

        {{-- Advanced Filters --}}
        @if($showFilters)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                {{-- Status Filter --}}
                <x-select
                    wire:model.live="statusFilter"
                    placeholder="All Statuses"
                    :options="collect($statusOptions)->map(fn($label, $value) => ['label' => $label, 'value' => $value])->values()"
                />
                
                {{-- Category Filter --}}
                <x-select
                    wire:model.live="categoryFilter"
                    placeholder="All Categories"
                    :options="$categories->map(fn($cat) => ['label' => $cat->name, 'value' => $cat->id])"
                />
                
                {{-- Author Filter --}}
                <x-select
                    wire:model.live="authorFilter"
                    placeholder="All Authors"
                    :options="$authors->map(fn($author) => ['label' => $author->name, 'value' => $author->id])"
                />
                
                {{-- Date Range --}}
                <div class="flex gap-2">
                    <x-input
                        wire:model.live="dateFrom"
                        type="date"
                        placeholder="From"
                        class="flex-1"
                    />
                    <x-input
                        wire:model.live="dateTo"
                        type="date"
                        placeholder="To"
                        class="flex-1"
                    />
                </div>
            </div>
        @endif
    </div>

    {{-- Bulk Actions --}}
    @if(count($selectedPosts) > 0)
        <div class="bg-blue-50 dark:bg-blue-900/20 backdrop-blur-sm rounded-xl p-4 mb-6 border border-blue-200/50 dark:border-blue-700/50">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    {{ count($selectedPosts) }} post{{ count($selectedPosts) === 1 ? '' : 's' }} selected
                </span>
                
                <div class="flex flex-wrap gap-2">
                    @can('posts.publish')
                        <x-button wire:click="bulkAction('publish')" outline positive size="sm">
                            Publish
                        </x-button>
                        <x-button wire:click="bulkAction('unpublish')" outline secondary size="sm">
                            Unpublish
                        </x-button>
                    @endcan
                    
                    @can('posts.feature')
                        <x-button wire:click="bulkAction('feature')" outline info size="sm">
                            Feature
                        </x-button>
                        <x-button wire:click="bulkAction('unfeature')" outline secondary size="sm">
                            Unfeature
                        </x-button>
                    @endcan
                    
                    <x-button wire:click="bulkAction('delete')" outline negative size="sm">
                        Delete
                    </x-button>
                </div>
            </div>
        </div>
    @endif

    {{-- Posts Table --}}
    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
        @if($posts->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/80">
                        <tr>
                            <th class="w-10 px-4 py-3">
                                <x-checkbox wire:model.live="selectAll" />
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('title')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                    Title
                                    @if($sortBy === 'title')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('status')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                    Status
                                    @if($sortBy === 'status')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">Category</th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('user.name')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                    Author
                                    @if($sortBy === 'user.name')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('views_count')" class="flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                    Views
                                    @if($sortBy === 'views_count')
                                        <x-icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                    @endif
                                </button>
                            </th>
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
                        @foreach($posts as $post)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-3">
                                    <x-checkbox wire:model.live="selectedPosts" value="{{ $post->id }}" />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-start gap-3">
                                        @if($post->featured_image)
                                            <img src="{{ asset('storage/' . $post->featured_image) }}" 
                                                 alt="{{ $post->title }}" 
                                                 class="w-12 h-8 object-cover rounded-md flex-shrink-0">
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <h3 class="font-medium text-gray-900 dark:text-white truncate">
                                                {{ $post->title }}
                                                @if($post->is_featured)
                                                    <x-icon name="star" class="w-4 h-4 text-yellow-500 inline ml-1" solid />
                                                @endif
                                            </h3>
                                            @if($post->excerpt)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                                    {{ $post->excerpt }}
                                                </p>
                                            @endif
                                            @if($post->tags->count() > 0)
                                                <div class="flex flex-wrap gap-1 mt-2">
                                                    @foreach($post->tags->take(3) as $tag)
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full {{ $tag->color_class }}">
                                                            {{ $tag->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($post->tags->count() > 3)
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                                            +{{ $post->tags->count() - 3 }} more
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full
                                        @if($post->status === 'published') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                        @elseif($post->status === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                        @elseif($post->status === 'scheduled') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                        @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                        @endif">
                                        {{ $post->status_label }}
                                    </span>
                                    @if($post->status === 'scheduled' && $post->published_at)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $post->published_at->format('M d, Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($post->category)
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            {{ $post->category->name }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Uncategorized</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if($post->user->avatar)
                                            <img src="{{ $post->user->avatar }}" alt="{{ $post->user->name }}" class="w-6 h-6 rounded-full">
                                        @else
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                                <span class="text-xs font-medium text-white">{{ substr($post->user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $post->user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-900 dark:text-white">
                                        {{ number_format($post->views_count ?: 0) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $post->created_at->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        {{-- Quick Actions --}}
                                        @if($post->status === 'draft')
                                            @can('posts.publish')
                                                <x-button wire:click="publishPost({{ $post->id }})" size="sm" positive title="Publish">
                                                    <x-icon name="arrow-up-tray" class="w-4 h-4" />
                                                </x-button>
                                            @endcan
                                        @elseif($post->status === 'published')
                                            @can('posts.unpublish')
                                                <x-button wire:click="unpublishPost({{ $post->id }})" size="sm" secondary title="Unpublish">
                                                    <x-icon name="arrow-down-tray" class="w-4 h-4" />
                                                </x-button>
                                            @endcan
                                        @endif
                                        
                                        @can('posts.feature')
                                            <x-button 
                                                wire:click="toggleFeatured({{ $post->id }})" 
                                                size="sm"
                                                title="{{ $post->is_featured ? 'Remove from featured' : 'Mark as featured' }}"
                                            >
                                                <x-icon name="star" class="w-4 h-4" {{ $post->is_featured ? 'solid' : '' }} />
                                            </x-button>
                                        @endcan
                                        
                                        {{-- Dropdown Menu --}}
                                        <x-dropdown>
                                            <x-slot name="trigger">
                                                <x-button size="sm" flat>
                                                    <x-icon name="ellipsis-vertical" class="w-4 h-4" />
                                                </x-button>
                                            </x-slot>
                                            
                                            <x-dropdown.item href="{{ route('admin.blog.posts.edit', $post) }}" icon="pencil">
                                                Edit
                                            </x-dropdown.item>
                                            
                                            @can('posts.create')
                                                <x-dropdown.item wire:click="duplicatePost({{ $post->id }})" icon="document-duplicate">
                                                    Duplicate
                                                </x-dropdown.item>
                                            @endcan
                                            
                                            <x-dropdown.item href="{{ $post->url }}" target="_blank" icon="eye">
                                                View
                                            </x-dropdown.item>
                                            
                                            <x-dropdown.item separator />
                                            
                                            <x-dropdown.item wire:click="deletePost({{ $post->id }})" icon="trash" class="text-red-600">
                                                Delete
                                            </x-dropdown.item>
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
                {{ $posts->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <x-icon name="document-text" class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No posts found</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    @if($search || $statusFilter || $categoryFilter || $authorFilter || $dateFrom || $dateTo)
                        Try adjusting your search criteria or filters.
                    @else
                        Get started by creating your first blog post.
                    @endif
                </p>
                @can('posts.create')
                    <x-button href="{{ route('admin.blog.posts.create') }}" primary icon="plus">
                        Create First Post
                    </x-button>
                @endcan
            </div>
        @endif
    </div>
</div>
