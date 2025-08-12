<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('admin.blog.categories.index') }}" 
                   class="inline-flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors">
                    <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
                    Back to Categories
                </a>
                
                <div class="flex items-center gap-2">
                    {{-- Status Badge --}}
                    @if($category->is_active)
                        <x-badge positive>Active</x-badge>
                    @else
                        <x-badge secondary>Inactive</x-badge>
                    @endif
                    
                    {{-- Parent Badge --}}
                    @if($category->parent)
                        <x-badge outline>
                            Child of {{ $category->parent->name }}
                        </x-badge>
                    @else
                        <x-badge primary>Root Category</x-badge>
                    @endif
                </div>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ $category->name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $category->posts_count ?? 0 }} {{ Str::plural('post', $category->posts_count ?? 0) }}
                @if($category->sort_order)
                    • Sort Order: {{ $category->sort_order }}
                @endif
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            @can('categories.update')
                <x-button href="{{ route('admin.blog.categories.edit', $category) }}" primary icon="pencil">
                    Edit Category
                </x-button>
            @endcan
        </div>
    </div>

    {{-- Category Details --}}
    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
        
        {{-- Category Image --}}
        @if($category->image)
            <div class="aspect-video bg-gray-100 dark:bg-gray-700 overflow-hidden">
                <img src="{{ $category->image }}" 
                     alt="{{ $category->name }}" 
                     class="w-full h-full object-cover">
            </div>
        @endif
        
        {{-- Category Meta --}}
        <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Hierarchy --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Hierarchy</h3>
                    @if($category->parent)
                        <div class="flex items-center gap-2">
                            <span style="background-color: {{ $category->parent->color }}" class="w-3 h-3 rounded-full"></span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $category->parent->name }}</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 ml-5">↳ {{ $category->name }}</div>
                    @else
                        <span class="text-sm text-gray-900 dark:text-white">Root Category</span>
                    @endif
                </div>
                
                {{-- Visual Settings --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Appearance</h3>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span style="background-color: {{ $category->color }}" class="w-4 h-4 rounded-full border border-gray-200 dark:border-gray-600"></span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $category->color }}</span>
                        </div>
                        @if($category->icon)
                            <div class="flex items-center gap-2">
                                <x-icon :name="$category->icon" class="w-4 h-4" />
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $category->icon }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                {{-- Statistics --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Statistics</h3>
                    <div class="text-sm text-gray-900 dark:text-white">
                        <div>{{ $category->posts_count ?? 0 }} {{ Str::plural('post', $category->posts_count ?? 0) }}</div>
                        @if($category->children && $category->children->count() > 0)
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $category->children->count() }} {{ Str::plural('subcategory', $category->children->count()) }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Description --}}
        @if($category->description)
            <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Description</h3>
                <div class="prose prose-gray dark:prose-invert max-w-none">
                    <p class="text-gray-900 dark:text-white">{{ $category->description }}</p>
                </div>
            </div>
        @endif
        
        {{-- SEO Information --}}
        @if($category->seo_title || $category->seo_description || $category->meta_keywords)
            <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-700/20">
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-4">SEO Information</h3>
                
                @if($category->seo_title)
                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">SEO Title</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $category->seo_title }}</p>
                    </div>
                @endif
                
                @if($category->seo_description)
                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">SEO Description</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $category->seo_description }}</p>
                    </div>
                @endif
                
                @if($category->meta_keywords)
                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Meta Keywords</label>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach(explode(',', $category->meta_keywords) as $keyword)
                                <x-badge outline>{{ trim($keyword) }}</x-badge>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">URL Slug</label>
                    <p class="text-sm text-gray-900 dark:text-white font-mono">{{ $category->slug }}</p>
                </div>
            </div>
        @endif
        
        {{-- Timestamps --}}
        <div class="p-6 bg-gray-50/50 dark:bg-gray-700/20">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-4">Category Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Created</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $category->created_at->format('M j, Y \a\t g:i A') }}
                        <span class="text-gray-500 dark:text-gray-400">({{ $category->created_at->diffForHumans() }})</span>
                    </p>
                </div>
                
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $category->updated_at->format('M j, Y \a\t g:i A') }}
                        <span class="text-gray-500 dark:text-gray-400">({{ $category->updated_at->diffForHumans() }})</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Subcategories --}}
    @if($category->children && $category->children->count() > 0)
        <div class="mt-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Subcategories</h2>
            
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
                    @foreach($category->children as $child)
                        <div class="p-4 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span style="background-color: {{ $child->color }}" class="w-3 h-3 rounded-full"></span>
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $child->name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $child->posts_count ?? 0 }} {{ Str::plural('post', $child->posts_count ?? 0) }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                @if($child->is_active)
                                    <x-badge positive>Active</x-badge>
                                @else
                                    <x-badge secondary>Inactive</x-badge>
                                @endif
                                
                                @can('categories.view')
                                    <x-button href="{{ route('admin.blog.categories.show', $child) }}" xs outline>
                                        View
                                    </x-button>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>