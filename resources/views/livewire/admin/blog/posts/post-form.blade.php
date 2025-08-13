<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $isEditing ? 'Edit Post' : 'Create New Post' }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $isEditing ? 'Update your blog post content and settings' : 'Create engaging content for your blog' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <x-button href="{{ route('admin.blog.posts.index') }}" outline icon="arrow-left">
                Back to Posts
            </x-button>

            {{-- Save Actions --}}
            <div class="flex items-center gap-2">
                <x-button wire:click="saveDraft" outline secondary>
                    Save Draft
                </x-button>

                @can('posts.publish')
                    <x-button wire:click="saveAndPublish" primary>
                        {{ $status === 'published' ? 'Update & Publish' : 'Publish' }}
                    </x-button>
                @endcan

                @can('posts.schedule')
                    <x-dropdown>
                        <x-slot name="trigger">
                            <x-button outline>
                                <x-icon name="clock" class="w-4 h-4 mr-1" />
                                Schedule
                                <x-icon name="chevron-down" class="w-4 h-4 ml-1" />
                            </x-button>
                        </x-slot>

                        <x-dropdown.item wire:click="schedulePost">
                            Schedule for Later
                        </x-dropdown.item>
                    </x-dropdown>
                @endcan
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Tab Navigation --}}
                <div
                    class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                    <div class="border-b border-gray-200/50 dark:border-gray-700/50">
                        <nav class="flex space-x-8 px-6" aria-label="Tabs">
                            <button type="button" wire:click="setActiveTab('content')"
                                class="py-4 px-1 border-b-2 font-medium text-sm transition-colors cursor-pointer {{ $activeTab === 'content' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                <x-icon name="document-text" class="w-4 h-4 mr-2 inline" />
                                Content
                            </button>
                            <button type="button" wire:click="setActiveTab('seo')"
                                class="py-4 px-1 border-b-2 font-medium text-sm transition-colors cursor-pointer {{ $activeTab === 'seo' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                <x-icon name="magnifying-glass" class="w-4 h-4 mr-2 inline" />
                                SEO
                            </button>
                            <button type="button" wire:click="setActiveTab('social')"
                                class="py-4 px-1 border-b-2 font-medium text-sm transition-colors cursor-pointer {{ $activeTab === 'social' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                <x-icon name="share" class="w-4 h-4 mr-2 inline" />
                                Social
                            </button>
                            <button type="button" wire:click="setActiveTab('advanced')"
                                class="py-4 px-1 border-b-2 font-medium text-sm transition-colors cursor-pointer {{ $activeTab === 'advanced' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                <x-icon name="cog-6-tooth" class="w-4 h-4 mr-2 inline" />
                                Advanced
                            </button>
                        </nav>
                    </div>

                    {{-- Tab Content --}}
                    <div class="p-6">
                        {{-- Content Tab --}}
                        @if ($activeTab === 'content')
                            <div class="space-y-6">
                                {{-- Title --}}
                                <div>
                                    <x-input wire:model.live.debounce.300ms="title" label="Post Title"
                                        placeholder="Enter your post title..." class="text-xl font-semibold" required />
                                </div>

                                {{-- Slug --}}
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <x-label for="slug" value="URL Slug" />
                                        <x-button type="button" wire:click="generateSlugFromTitle" flat size="sm">
                                            <x-icon name="arrow-path" class="w-3 h-3 mr-1" />
                                            Generate
                                        </x-button>
                                    </div>
                                    <x-input wire:model="slug" id="slug" placeholder="url-friendly-slug"
                                        class="font-mono text-sm" required />
                                    @if ($slug)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Preview: {{ url('/blog/' . $slug) }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Excerpt --}}
                                <div>
                                    <x-textarea wire:model.live.debounce.300ms="excerpt" label="Excerpt"
                                        placeholder="Brief description of your post (auto-generated if empty)..."
                                        rows="3" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ strlen($excerpt ?? '') }}/500 characters
                                    </p>
                                </div>

                                {{-- Content Editor --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content *</label>
                                    <div wire:ignore>
                                        <div id="quill-editor" style="height: 400px;"></div>
                                    </div>
                                    <input type="hidden" wire:model="content" id="content-input">
                                    @error('content') 
                                        <span class="text-red-500 text-sm">{{ $message }}</span> 
                                    @enderror
                                </div>

                                {{-- Featured Image --}}
                                <div>
                                    <x-label for="featured_image_upload" value="Featured Image" />
                                    <div class="mt-2">
                                        @if ($current_featured_image)
                                            <div class="mb-4">
                                                <img src="{{ asset('storage/' . $current_featured_image) }}"
                                                    alt="Current featured image"
                                                    class="w-32 h-20 object-cover rounded-lg">
                                                <p class="text-xs text-gray-500 mt-1">Current featured image</p>
                                            </div>
                                        @endif

                                        <input type="file" wire:model="featured_image_upload"
                                            id="featured_image_upload" accept="image/*"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/20 dark:file:text-blue-400">

                                        @if ($featured_image_upload)
                                            <div class="mt-2">
                                                <img src="{{ $featured_image_upload->temporaryUrl() }}" alt="Preview"
                                                    class="w-32 h-20 object-cover rounded-lg">
                                                <p class="text-xs text-gray-500 mt-1">New image preview</p>
                                            </div>
                                        @endif

                                        @error('featured_image_upload')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Tags --}}
                                <div>
                                    <x-label for="tag-input" value="Tags" />
                                    <div class="mt-2 space-y-3">
                                        {{-- Selected Tags --}}
                                        @if (count($selectedTags) > 0)
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($selectedTagsModels as $tag)
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full {{ $tag->color_class }}">
                                                        {{ $tag->name }}
                                                        <button type="button"
                                                            wire:click="removeTag({{ $tag->id }})"
                                                            class="ml-2 text-current hover:text-red-600 cursor-pointer">
                                                            <x-icon name="x-mark" class="w-3 h-3" />
                                                        </button>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Tag Input --}}
                                        <div class="relative">
                                            <x-input wire:model.live.debounce.300ms="tagInput" id="tag-input"
                                                placeholder="Type to search or create tags..."
                                                wire:keydown.enter.prevent="createTagFromInput" />

                                            {{-- Tag Suggestions --}}
                                            @if (count($suggestedTags) > 0)
                                                <div
                                                    class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg">
                                                    @foreach ($suggestedTags as $tag)
                                                        <button type="button"
                                                            wire:click="addTag({{ $tag->id }})"
                                                            class="w-full px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 first:rounded-t-lg last:rounded-b-lg cursor-pointer">
                                                            <span
                                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full {{ $tag->color_class }} mr-2">
                                                                {{ $tag->name }}
                                                            </span>
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                                {{ $tag->posts_count ?? 0 }} posts
                                                            </span>
                                                        </button>
                                                    @endforeach

                                                    @if ($tagInput)
                                                        <div
                                                            class="border-t border-gray-200 dark:border-gray-700 px-3 py-2">
                                                            <button type="button" wire:click="createTagFromInput"
                                                                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 cursor-pointer">
                                                                <x-icon name="plus" class="w-3 h-3 mr-1 inline" />
                                                                Create "{{ $tagInput }}"
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- SEO Tab --}}
                        @if ($activeTab === 'seo')
                            <div class="space-y-6">
                                <div
                                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-700/50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <x-icon name="information-circle"
                                            class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" />
                                        <div>
                                            <h3 class="font-medium text-blue-800 dark:text-blue-200">SEO Optimization
                                            </h3>
                                            <p class="text-sm text-blue-600 dark:text-blue-300 mt-1">
                                                If SEO fields are left empty, they will auto-generate from your title
                                                and content.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <x-input wire:model="seo_title" label="SEO Title"
                                        placeholder="Optimized title for search engines..." maxlength="60" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ strlen($seo_title ?? '') }}/60 characters
                                    </p>
                                </div>

                                <div>
                                    <x-textarea wire:model="seo_description" label="SEO Description"
                                        placeholder="Brief description for search engine results..." rows="3"
                                        maxlength="160" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ strlen($seo_description ?? '') }}/160 characters
                                    </p>
                                </div>

                                <div>
                                    <x-input wire:model="meta_keywords" label="Meta Keywords"
                                        placeholder="keyword1, keyword2, keyword3..." />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Separate keywords with commas
                                    </p>
                                </div>

                                {{-- SEO Preview --}}
                                <div
                                    class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200/50 dark:border-gray-700/50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-3">Search Preview</h4>
                                    <div class="space-y-2">
                                        <div class="text-blue-600 dark:text-blue-400 text-lg font-medium line-clamp-1">
                                            {{ $seo_title ?: $title ?: 'Your Post Title' }}
                                        </div>
                                        <div class="text-green-700 dark:text-green-400 text-sm">
                                            {{ url('/blog/' . ($slug ?: 'your-post-slug')) }}
                                        </div>
                                        <div class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2">
                                            {{ $seo_description ?: $excerpt ?: 'Your post description will appear here...' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Social Tab --}}
                        @if ($activeTab === 'social')
                            <div class="space-y-6">
                                <div
                                    class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200/50 dark:border-purple-700/50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <x-icon name="share" class="w-5 h-5 text-purple-500 flex-shrink-0 mt-0.5" />
                                        <div>
                                            <h3 class="font-medium text-purple-800 dark:text-purple-200">Social Media
                                            </h3>
                                            <p class="text-sm text-purple-600 dark:text-purple-300 mt-1">
                                                Customize how your post appears when shared on social media.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <x-input wire:model="og_image" label="Open Graph Image URL"
                                        placeholder="https://example.com/image.jpg" type="url" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Recommended size: 1200x630px. If empty, featured image will be used.
                                    </p>
                                </div>

                                <div>
                                    <x-select wire:model="twitter_card" label="Twitter Card Type" :option-label="'label'"
                                        :option-value="'value'" :options="collect($twitterCardOptions)
                                            ->map(fn($label, $value) => ['label' => $label, 'value' => $value])
                                            ->values()" />
                                </div>

                                {{-- Social Preview --}}
                                <div
                                    class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200/50 dark:border-gray-700/50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-3">Social Preview</h4>
                                    <div
                                        class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden max-w-md">
                                        @if ($og_image || $current_featured_image)
                                            <div
                                                class="aspect-video bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                                @if ($og_image)
                                                    <img src="{{ $og_image }}" alt="OG Image"
                                                        class="w-full h-full object-cover">
                                                @elseif($current_featured_image)
                                                    <img src="{{ asset('storage/' . $current_featured_image) }}"
                                                        alt="Featured Image" class="w-full h-full object-cover">
                                                @endif
                                            </div>
                                        @endif
                                        <div class="p-3">
                                            <div class="font-medium text-gray-900 dark:text-white line-clamp-1">
                                                {{ $title ?: 'Your Post Title' }}
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                                {{ $excerpt ?: 'Your post description...' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                                {{ parse_url(url('/'), PHP_URL_HOST) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Advanced Tab --}}
                        @if ($activeTab === 'advanced')
                            <div class="space-y-6">
                                <div>
                                    <x-textarea wire:model="custom_css" label="Custom CSS"
                                        placeholder="/* Custom styles for this post */" rows="6"
                                        class="font-mono text-sm" />
                                </div>

                                <div>
                                    <x-textarea wire:model="custom_js" label="Custom JavaScript"
                                        placeholder="// Custom scripts for this post" rows="6"
                                        class="font-mono text-sm" />
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Publish Settings --}}
                <div
                    class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 border border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Publish Settings</h3>

                    <div class="space-y-4">
                        <div>
                            <x-select wire:model="status" label="Status" :option-label="'label'" :option-value="'value'"
                                :options="collect($statusOptions)
                                    ->map(fn($label, $value) => ['label' => $label, 'value' => $value])
                                    ->values()" />
                        </div>

                        <div>
                            <x-input wire:model="published_at" label="Publish Date" type="datetime-local" />
                        </div>

                        <div>
                            <x-select wire:model="category_id" label="Category" placeholder="Select category..."
                                :option-label="'label'" :option-value="'value'" :options="$categories->map(fn($cat) => ['label' => $cat->name, 'value' => $cat->id])" required />
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center">
                                <x-checkbox wire:model="is_featured" id="is_featured" />
                                <x-label for="is_featured" class="ml-2">Featured Post</x-label>
                            </div>

                            <div class="flex items-center">
                                <x-checkbox wire:model="allow_comments" id="allow_comments" />
                                <x-label for="allow_comments" class="ml-2">Allow Comments</x-label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Post Stats (for editing) --}}
                @if ($isEditing)
                    <div
                        class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 border border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Post Statistics</h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Views:</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ number_format($post->views_count ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Reading Time:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $post->reading_time ?? 1 }}
                                    min</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Created:</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ $post->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Updated:</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ $post->updated_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Help & Tips --}}
                <div
                    class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200/50 dark:border-blue-700/50">
                    <h3 class="text-lg font-medium text-blue-800 dark:text-blue-200 mb-3">
                        <x-icon name="light-bulb" class="w-5 h-5 mr-2 inline" />
                        Writing Tips
                    </h3>

                    <div class="space-y-2 text-sm text-blue-700 dark:text-blue-300">
                        <p>• Use headings to structure your content</p>
                        <p>• Add images to break up long text</p>
                        <p>• Keep paragraphs short and readable</p>
                        <p>• Use bullet points for lists</p>
                        <p>• Optimize your SEO title and description</p>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Quill editor
    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                [{ 'align': [] }],
                ['clean']
            ]
        },
        placeholder: 'Write your blog post content here...'
    });

    // Set initial content if editing
    @if($content)
        quill.root.innerHTML = @json($content);
    @endif

    // Custom image handler
    quill.getModule('toolbar').addHandler('image', function () {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = function () {
            var file = input.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(event) {
                    var base64Data = event.target.result;
                    @this.uploadImage(base64Data);
                };
                reader.readAsDataURL(file);
            }
        };
    });

    // Track previous images for deletion detection
    let previousImages = [];

    // Update Livewire model on text change
    quill.on('text-change', function(delta, oldDelta, source) {
        document.getElementById('content-input').value = quill.root.innerHTML;
        @this.set('content', quill.root.innerHTML);

        // Track image changes for deletion
        var currentImages = [];
        var container = quill.container.firstChild;
        
        container.querySelectorAll('img').forEach(function(img) {
            currentImages.push(img.src);
        });

        var removedImages = previousImages.filter(function(image) {
            return !currentImages.includes(image);
        });

        removedImages.forEach(function(image) {
            @this.deleteImage(image);
        });

        previousImages = currentImages;
    });

    // Listen for successful image upload
    Livewire.on('blogimageUploaded', function(imagePath) {
        if (imagePath) {
            var range = quill.getSelection(true);
            var index = range ? range.index : quill.getLength();
            quill.insertEmbed(index, 'image', imagePath);
            quill.setSelection(index + 1);
        }
    });

    // Update editor content when Livewire content changes
    Livewire.on('contentUpdated', function(content) {
        quill.root.innerHTML = content;
    });
});
</script>
@endpush
