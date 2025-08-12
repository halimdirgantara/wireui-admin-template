<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('admin.blog.posts.index') }}" 
                   class="inline-flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors">
                    <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
                    Back to Posts
                </a>
                
                <div class="flex items-center gap-2">
                    {{-- Status Badge --}}
                    @if($post->status === 'published')
                        <x-badge positive>Published</x-badge>
                    @elseif($post->status === 'draft')
                        <x-badge secondary>Draft</x-badge>
                    @else
                        <x-badge warning>Pending</x-badge>
                    @endif
                    
                    {{-- Featured Badge --}}
                    @if($post->is_featured)
                        <x-badge primary>Featured</x-badge>
                    @endif
                </div>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ $post->title }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                by <span class="font-medium">{{ $post->author->name }}</span> • 
                {{ $post->created_at->diffForHumans() }} •
                {{ $post->views_count ?? 0 }} views
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            @can('posts.update')
                <x-button href="{{ route('admin.blog.posts.edit', $post) }}" primary icon="pencil">
                    Edit Post
                </x-button>
            @endcan
        </div>
    </div>

    {{-- Post Content --}}
    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
        
        {{-- Featured Image --}}
        @if($post->featured_image)
            <div class="aspect-video bg-gray-100 dark:bg-gray-700 overflow-hidden">
                <img src="{{ $post->featured_image }}" 
                     alt="{{ $post->title }}" 
                     class="w-full h-full object-cover">
            </div>
        @endif
        
        {{-- Post Meta --}}
        <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Category --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Category</h3>
                    @if($post->category)
                        <x-badge>{{ $post->category->name }}</x-badge>
                    @else
                        <span class="text-gray-500 dark:text-gray-400 text-sm">No category</span>
                    @endif
                </div>
                
                {{-- Tags --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Tags</h3>
                    @if($post->tags->count() > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach($post->tags as $tag)
                                <x-badge outline>{{ $tag->name }}</x-badge>
                            @endforeach
                        </div>
                    @else
                        <span class="text-gray-500 dark:text-gray-400 text-sm">No tags</span>
                    @endif
                </div>
                
                {{-- SEO Score --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">SEO Score</h3>
                    @if($post->seo_score)
                        <div class="flex items-center gap-2">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-gradient-to-r from-red-500 via-yellow-500 to-green-500 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ $post->seo_score }}%"></div>
                            </div>
                            <span class="text-sm font-medium">{{ $post->seo_score }}%</span>
                        </div>
                    @else
                        <span class="text-gray-500 dark:text-gray-400 text-sm">Not analyzed</span>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Post Content --}}
        <div class="p-6">
            {{-- Excerpt --}}
            @if($post->excerpt)
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 rounded-r-lg">
                    <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Excerpt</h3>
                    <p class="text-blue-700 dark:text-blue-200 italic">{{ $post->excerpt }}</p>
                </div>
            @endif
            
            {{-- Main Content --}}
            <div class="prose prose-gray dark:prose-invert max-w-none">
                {!! $post->content !!}
            </div>
        </div>
        
        {{-- SEO Meta --}}
        @if($post->meta_title || $post->meta_description)
            <div class="p-6 border-t border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-700/20">
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-4">SEO Meta Information</h3>
                
                @if($post->meta_title)
                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Meta Title</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $post->meta_title }}</p>
                    </div>
                @endif
                
                @if($post->meta_description)
                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Meta Description</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $post->meta_description }}</p>
                    </div>
                @endif
                
                @if($post->slug)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">URL Slug</label>
                        <p class="text-sm text-gray-900 dark:text-white font-mono">{{ $post->slug }}</p>
                    </div>
                @endif
            </div>
        @endif
        
        {{-- Publishing Info --}}
        <div class="p-6 border-t border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-700/20">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-4">Publishing Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Created</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $post->created_at->format('M j, Y \a\t g:i A') }}
                        <span class="text-gray-500 dark:text-gray-400">({{ $post->created_at->diffForHumans() }})</span>
                    </p>
                </div>
                
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $post->updated_at->format('M j, Y \a\t g:i A') }}
                        <span class="text-gray-500 dark:text-gray-400">({{ $post->updated_at->diffForHumans() }})</span>
                    </p>
                </div>
                
                @if($post->published_at)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Published</label>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $post->published_at->format('M j, Y \a\t g:i A') }}
                            <span class="text-gray-500 dark:text-gray-400">({{ $post->published_at->diffForHumans() }})</span>
                        </p>
                    </div>
                @endif
                
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Reading Time</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $post->reading_time ?? 'N/A' }} {{ $post->reading_time == 1 ? 'minute' : 'minutes' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>