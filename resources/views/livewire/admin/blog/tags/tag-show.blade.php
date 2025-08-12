<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('admin.blog.tags.index') }}" 
                   class="inline-flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors">
                    <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
                    Back to Tags
                </a>
                
                <div class="flex items-center gap-2">
                    {{-- Status Badge --}}
                    @if($tag->is_active)
                        <x-badge positive>Active</x-badge>
                    @else
                        <x-badge secondary>Inactive</x-badge>
                    @endif
                    
                    {{-- Tag Preview --}}
                    <span 
                        class="px-2 py-1 text-xs font-medium rounded text-white"
                        style="background-color: {{ $tag->color }}"
                    >
                        {{ $tag->name }}
                    </span>
                </div>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ $tag->name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $tag->posts_count ?? 0 }} {{ Str::plural('post', $tag->posts_count ?? 0) }} tagged
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            @can('tags.update')
                <x-button href="{{ route('admin.blog.tags.edit', $tag) }}" primary icon="pencil">
                    Edit Tag
                </x-button>
            @endcan
        </div>
    </div>

    {{-- Tag Details --}}
    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
        
        {{-- Tag Meta --}}
        <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Basic Info --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Tag Information</h3>
                    <div class="space-y-2">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Name</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $tag->name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Slug</label>
                            <p class="text-sm text-gray-900 dark:text-white font-mono">{{ $tag->slug }}</p>
                        </div>
                    </div>
                </div>
                
                {{-- Appearance --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Appearance</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span style="background-color: {{ $tag->color }}" class="w-4 h-4 rounded border border-gray-200 dark:border-gray-600"></span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $tag->color }}</span>
                        </div>
                        <div>
                            <span 
                                class="px-3 py-1 text-sm font-medium rounded text-white"
                                style="background-color: {{ $tag->color }}"
                            >
                                {{ $tag->name }}
                            </span>
                        </div>
                    </div>
                </div>
                
                {{-- Statistics --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Usage Statistics</h3>
                    <div class="space-y-2">
                        <div>
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $tag->posts_count ?? 0 }}</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::plural('post', $tag->posts_count ?? 0) }} tagged</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Description --}}
        @if($tag->description)
            <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Description</h3>
                <div class="prose prose-gray dark:prose-invert max-w-none">
                    <p class="text-gray-900 dark:text-white">{{ $tag->description }}</p>
                </div>
            </div>
        @endif
        
        {{-- Timestamps --}}
        <div class="p-6 bg-gray-50/50 dark:bg-gray-700/20">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-4">Tag Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Created</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $tag->created_at->format('M j, Y \a\t g:i A') }}
                        <span class="text-gray-500 dark:text-gray-400">({{ $tag->created_at->diffForHumans() }})</span>
                    </p>
                </div>
                
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $tag->updated_at->format('M j, Y \a\t g:i A') }}
                        <span class="text-gray-500 dark:text-gray-400">({{ $tag->updated_at->diffForHumans() }})</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Posts --}}
    @if($tag->posts && $tag->posts->count() > 0)
        <div class="mt-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Recent Posts with this Tag</h2>
            
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                <div class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
                    @foreach($tag->posts->take(10) as $post)
                        <div class="p-4 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $post->title }}</h4>
                                <div class="flex items-center gap-4 mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    <span>by {{ $post->author->name }}</span>
                                    <span>{{ $post->created_at->format('M j, Y') }}</span>
                                    @if($post->status === 'published')
                                        <x-badge positive xs>Published</x-badge>
                                    @elseif($post->status === 'draft')
                                        <x-badge secondary xs>Draft</x-badge>
                                    @else
                                        <x-badge warning xs>{{ ucfirst($post->status) }}</x-badge>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 ml-4">
                                @can('posts.view')
                                    <x-button href="{{ route('admin.blog.posts.show', $post) }}" xs outline>
                                        View
                                    </x-button>
                                @endcan
                                
                                @can('posts.update')
                                    <x-button href="{{ route('admin.blog.posts.edit', $post) }}" xs primary>
                                        Edit
                                    </x-button>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($tag->posts->count() > 10)
                    <div class="p-4 bg-gray-50/50 dark:bg-gray-700/20 text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Showing 10 of {{ $tag->posts->count() }} posts. 
                            <a href="{{ route('admin.blog.posts.index', ['tag' => $tag->id]) }}" 
                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                View all posts with this tag →
                            </a>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="mt-6">
            <div class="bg-gray-50/50 dark:bg-gray-700/20 rounded-xl p-8 text-center border border-gray-200/50 dark:border-gray-700/50">
                <div class="text-gray-400 dark:text-gray-500 mb-4">
                    <x-icon name="tag" class="w-12 h-12 mx-auto" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Posts Yet</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    This tag hasn't been used in any posts yet.
                </p>
                @can('posts.create')
                    <x-button href="{{ route('admin.blog.posts.create') }}" primary>
                        Create First Post
                    </x-button>
                @endcan
            </div>
        </div>
    @endif
</div>