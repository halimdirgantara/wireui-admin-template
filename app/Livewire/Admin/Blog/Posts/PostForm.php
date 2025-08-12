<?php

namespace App\Livewire\Admin\Blog\Posts;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithFileUploads;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PostForm extends Component
{
    use WithFileUploads, WireUiActions, AuthorizesRequests;

    public Post $post;
    public bool $isEditing = false;

    // Basic post fields
    public $title = '';
    public $slug = '';
    public $excerpt = '';
    public $content = '';
    public $status = 'draft';
    public $published_at = '';
    public $category_id = '';
    public $is_featured = false;
    public $allow_comments = true;

    // SEO fields
    public $seo_title = '';
    public $seo_description = '';
    public $meta_keywords = '';

    // Social media fields
    public $og_image = '';
    public $twitter_card = 'summary_large_image';

    // Custom CSS/JS
    public $custom_css = '';
    public $custom_js = '';

    // Featured image
    public $featured_image_upload;
    public $current_featured_image = '';

    // Tags
    public $selectedTags = [];
    public $tagInput = '';
    public $suggestedTags = [];

    // UI state
    public $activeTab = 'content';

    protected $rules = [
        'title' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:posts,slug',
        'excerpt' => 'nullable|string|max:500',
        'content' => 'required|string',
        'status' => 'required|in:draft,published,scheduled,archived',
        'published_at' => 'nullable|date',
        'category_id' => 'required|exists:categories,id',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'seo_title' => 'nullable|string|max:60',
        'seo_description' => 'nullable|string|max:160',
        'meta_keywords' => 'nullable|string|max:255',
        'og_image' => 'nullable|url',
        'twitter_card' => 'nullable|in:summary,summary_large_image,app,player',
        'custom_css' => 'nullable|string',
        'custom_js' => 'nullable|string',
        'featured_image_upload' => 'nullable|image|max:5120', // 5MB
        'selectedTags' => 'array',
        'selectedTags.*' => 'integer|exists:tags,id',
    ];

    protected $messages = [
        'title.required' => 'The post title is required.',
        'content.required' => 'The post content is required.',
        'category_id.required' => 'Please select a category.',
        'category_id.exists' => 'The selected category is invalid.',
        'seo_title.max' => 'SEO title should not exceed 60 characters.',
        'seo_description.max' => 'SEO description should not exceed 160 characters.',
        'featured_image_upload.max' => 'Featured image must be less than 5MB.',
    ];

    public function mount($postId = null)
    {
        if ($postId) {
            $this->post = Post::with('tags')->findOrFail($postId);
            $this->isEditing = true;

            if (!$this->canUpdatePost($this->post)) {
                abort(403, 'Unauthorized to edit this post.');
            }

            $this->loadPostData();
        } else {
            $this->authorize('posts.create');
            $this->post = new Post();
            $this->published_at = now()->format('Y-m-d\TH:i');
        }
    }

    private function loadPostData()
    {
        $this->title = $this->post->title;
        $this->slug = $this->post->slug;
        $this->excerpt = $this->post->excerpt;
        $this->content = $this->post->content;
        $this->status = $this->post->status;
        $this->published_at = $this->post->published_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
        $this->category_id = $this->post->category_id;
        $this->is_featured = $this->post->is_featured;
        $this->allow_comments = $this->post->allow_comments;
        $this->seo_title = $this->post->seo_title;
        $this->seo_description = $this->post->seo_description;
        $this->meta_keywords = $this->post->meta_keywords;
        $this->og_image = $this->post->og_image;
        $this->twitter_card = $this->post->twitter_card ?? 'summary_large_image';
        $this->custom_css = $this->post->custom_css;
        $this->custom_js = $this->post->custom_js;
        $this->current_featured_image = $this->post->featured_image;
        $this->selectedTags = $this->post->tags->pluck('id')->toArray();
    }

    public function updatedTitle($value)
    {
        if (!$this->isEditing || empty($this->slug)) {
            $this->slug = Str::slug($value);
        }

        if (empty($this->seo_title)) {
            $this->seo_title = Str::limit($value, 60);
        }
    }

    public function updatedContent($value)
    {
        if (empty($this->excerpt)) {
            $this->excerpt = Str::limit(strip_tags($value), 160);
        }

        if (empty($this->seo_description)) {
            $this->seo_description = Str::limit(strip_tags($value), 160);
        }
    }

    public function updatedTagInput($value)
    {
        if (strlen($value) >= 2) {
            $this->suggestedTags = Tag::where('name', 'like', '%' . $value . '%')
                ->whereNotIn('id', $this->selectedTags)
                ->limit(5)
                ->get();
        } else {
            $this->suggestedTags = [];
        }
    }

    public function addTag($tagId = null, $tagName = null)
    {
        if ($tagId) {
            // Add existing tag
            if (!in_array($tagId, $this->selectedTags)) {
                $this->selectedTags[] = $tagId;
            }
        } elseif ($tagName) {
            // Create new tag and add it
            $tag = Tag::firstOrCreate(['name' => $tagName], [
                'slug' => Str::slug($tagName),
                'color' => '#3b82f6',
                'is_active' => true
            ]);

            if (!in_array($tag->id, $this->selectedTags)) {
                $this->selectedTags[] = $tag->id;
            }
        }

        $this->tagInput = '';
        $this->suggestedTags = [];
    }

    public function removeTag($tagId)
    {
        $this->selectedTags = array_filter($this->selectedTags, fn($id) => $id != $tagId);
    }

    public function createTagFromInput()
    {
        if (!empty($this->tagInput)) {
            $this->addTag(null, $this->tagInput);
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function generateSlugFromTitle()
    {
        if ($this->title) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function saveDraft()
    {
        $this->status = Post::STATUS_DRAFT;
        $this->save();
    }

    public function saveAndPublish()
    {
        $this->authorize('posts.publish');

        $this->status = Post::STATUS_PUBLISHED;
        if (empty($this->published_at)) {
            $this->published_at = now()->format('Y-m-d\TH:i');
        }

        $this->save();
    }

    public function schedulePost()
    {
        $this->authorize('posts.schedule');

        $this->validate([
            'published_at' => 'required|date|after:now'
        ], [
            'published_at.after' => 'Scheduled date must be in the future.'
        ]);

        $this->status = Post::STATUS_SCHEDULED;
        $this->save();
    }

    public function save()
    {
        // Adjust slug validation for editing
        if ($this->isEditing) {
            $this->rules['slug'] = 'required|string|max:255|unique:posts,slug,' . $this->post->id;
        }

        $this->validate();

        try {
            // Handle file upload
            if ($this->featured_image_upload) {
                $path = $this->featured_image_upload->store('blog/featured-images', 'public');
                $this->current_featured_image = $path;
            }

            // Prepare data for saving
            $data = [
                'title' => $this->title,
                'slug' => $this->slug,
                'excerpt' => $this->excerpt,
                'content' => $this->content,
                'status' => $this->status,
                'published_at' => $this->published_at ? Carbon::parse($this->published_at) : null,
                'category_id' => $this->category_id,
                'is_featured' => $this->is_featured,
                'allow_comments' => $this->allow_comments,
                'seo_title' => $this->seo_title,
                'seo_description' => $this->seo_description,
                'meta_keywords' => $this->meta_keywords,
                'og_image' => $this->og_image,
                'twitter_card' => $this->twitter_card,
                'custom_css' => $this->custom_css,
                'custom_js' => $this->custom_js,
                'featured_image' => $this->current_featured_image,
            ];

            if ($this->isEditing) {
                $this->post->update($data);
            } else {
                $data['user_id'] = auth()->id();
                $this->post = Post::create($data);
            }

            // Sync tags
            $this->post->tags()->sync($this->selectedTags);

            $action = $this->isEditing ? 'updated' : 'created';
            $this->notification()->success(
                'Success',
                "Post has been {$action} successfully."
            );

            // Redirect to posts index
            return redirect()->route('admin.blog.posts.index');

        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'An error occurred while saving the post. Please try again.'
            );
        }
    }

    public function getCategoriesProperty()
    {
        return Category::active()->orderBy('name')->get();
    }

    public function getSelectedTagsModelsProperty()
    {
        return Tag::whereIn('id', $this->selectedTags)->get();
    }

    public function getStatusOptionsProperty()
    {
        return Post::getStatusOptions();
    }

    public function getTwitterCardOptionsProperty()
    {
        return [
            'summary' => 'Summary',
            'summary_large_image' => 'Summary with Large Image',
            'app' => 'App',
            'player' => 'Player'
        ];
    }

    private function canUpdatePost(Post $post): bool
    {
        return auth()->user()->can('posts.update-all') ||
               (auth()->user()->can('posts.update-own') && $post->user_id === auth()->id());
    }

    public function render()
    {
        return view('livewire.admin.blog.posts.post-form', [
            'categories' => $this->categories,
            'selectedTagsModels' => $this->selectedTagsModels,
            'statusOptions' => $this->statusOptions,
            'twitterCardOptions' => $this->twitterCardOptions,
        ])->layout('layouts.admin');
    }
}
