<?php

namespace App\Livewire\Admin\Blog\Posts;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\Actions;
use Carbon\Carbon;

class PostIndex extends Component
{
    use WithPagination, Actions, AuthorizesRequests;

    public $search = '';
    public $statusFilter = '';
    public $categoryFilter = '';
    public $authorFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    public $selectedPosts = [];
    public $selectAll = false;

    public $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'authorFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->authorize('posts.view');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingAuthorFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPosts = $this->posts->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedPosts = [];
        }
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->reset([
            'search', 
            'statusFilter', 
            'categoryFilter', 
            'authorFilter', 
            'dateFrom', 
            'dateTo'
        ]);
        $this->resetPage();
    }

    public function getPostsProperty()
    {
        $query = Post::query()
            ->with(['user', 'category', 'tags'])
            ->withCount('views');

        // Apply permission-based filtering
        if (!$this->canViewAllPosts()) {
            $query->where('user_id', auth()->id());
        }

        // Apply search
        if ($this->search) {
            $query->multiColumnSearch($this->search);
        }

        // Apply filters
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->authorFilter) {
            $query->where('user_id', $this->authorFilter);
        }

        // Apply date range
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function getCategoriesProperty()
    {
        return Category::active()->orderBy('name')->get();
    }

    public function getAuthorsProperty()
    {
        $query = User::whereHas('posts');
        
        if (!$this->canViewAllPosts()) {
            $query->where('id', auth()->id());
        }
        
        return $query->orderBy('name')->get();
    }

    public function getStatusOptionsProperty()
    {
        return Post::getStatusOptions();
    }

    public function publishPost($postId)
    {
        $this->authorize('posts.publish');
        
        $post = Post::findOrFail($postId);
        
        if (!$this->canUpdatePost($post)) {
            $this->notification()->error('Unauthorized', 'You cannot publish this post.');
            return;
        }

        $post->publish();
        
        $this->notification()->success(
            'Success', 
            "Post '{$post->title}' has been published."
        );
    }

    public function unpublishPost($postId)
    {
        $this->authorize('posts.unpublish');
        
        $post = Post::findOrFail($postId);
        
        if (!$this->canUpdatePost($post)) {
            $this->notification()->error('Unauthorized', 'You cannot unpublish this post.');
            return;
        }

        $post->makeDraft();
        
        $this->notification()->success(
            'Success', 
            "Post '{$post->title}' has been unpublished."
        );
    }

    public function toggleFeatured($postId)
    {
        $this->authorize('posts.feature');
        
        $post = Post::findOrFail($postId);
        
        if (!$this->canUpdatePost($post)) {
            $this->notification()->error('Unauthorized', 'You cannot modify this post.');
            return;
        }

        $post->update(['is_featured' => !$post->is_featured]);
        
        $action = $post->is_featured ? 'featured' : 'unfeatured';
        $this->notification()->success(
            'Success', 
            "Post '{$post->title}' has been {$action}."
        );
    }

    public function duplicatePost($postId)
    {
        $this->authorize('posts.create');
        
        $originalPost = Post::with('tags')->findOrFail($postId);
        
        if (!$this->canViewPost($originalPost)) {
            $this->notification()->error('Unauthorized', 'You cannot duplicate this post.');
            return;
        }

        $newPost = $originalPost->replicate();
        $newPost->title = $originalPost->title . ' (Copy)';
        $newPost->slug = null; // Let the model generate a new slug
        $newPost->status = Post::STATUS_DRAFT;
        $newPost->published_at = null;
        $newPost->views_count = 0;
        $newPost->user_id = auth()->id();
        $newPost->save();

        // Sync tags
        $newPost->tags()->sync($originalPost->tags->pluck('id'));

        $this->notification()->success(
            'Success', 
            "Post '{$originalPost->title}' has been duplicated."
        );

        return redirect()->route('admin.posts.edit', $newPost);
    }

    public function deletePost($postId)
    {
        $post = Post::findOrFail($postId);
        
        if (!$this->canDeletePost($post)) {
            $this->notification()->error('Unauthorized', 'You cannot delete this post.');
            return;
        }

        $this->dialog()->confirm([
            'title' => 'Delete Post',
            'description' => "Are you sure you want to delete the post '{$post->title}'? This action cannot be undone.",
            'acceptLabel' => 'Delete',
            'method' => 'confirmDelete',
            'params' => $postId,
        ]);
    }

    public function confirmDelete($postId)
    {
        $post = Post::findOrFail($postId);
        $title = $post->title;
        
        $post->delete();
        
        $this->notification()->success(
            'Success', 
            "Post '{$title}' has been deleted."
        );

        // Remove from selected posts if it was selected
        $this->selectedPosts = array_filter($this->selectedPosts, fn($id) => $id != $postId);
    }

    public function bulkAction($action)
    {
        if (empty($this->selectedPosts)) {
            $this->notification()->warning('No Selection', 'Please select posts to perform bulk action.');
            return;
        }

        match($action) {
            'publish' => $this->bulkPublish(),
            'unpublish' => $this->bulkUnpublish(),
            'delete' => $this->bulkDelete(),
            'feature' => $this->bulkFeature(),
            'unfeature' => $this->bulkUnfeature(),
            default => null
        };
    }

    private function bulkPublish()
    {
        $this->authorize('posts.publish');
        
        $posts = Post::whereIn('id', $this->selectedPosts)->get();
        $count = 0;
        
        foreach ($posts as $post) {
            if ($this->canUpdatePost($post)) {
                $post->publish();
                $count++;
            }
        }
        
        $this->selectedPosts = [];
        $this->selectAll = false;
        
        $this->notification()->success('Success', "{$count} posts have been published.");
    }

    private function bulkUnpublish()
    {
        $this->authorize('posts.unpublish');
        
        $posts = Post::whereIn('id', $this->selectedPosts)->get();
        $count = 0;
        
        foreach ($posts as $post) {
            if ($this->canUpdatePost($post)) {
                $post->makeDraft();
                $count++;
            }
        }
        
        $this->selectedPosts = [];
        $this->selectAll = false;
        
        $this->notification()->success('Success', "{$count} posts have been unpublished.");
    }

    private function bulkDelete()
    {
        $posts = Post::whereIn('id', $this->selectedPosts)->get();
        $count = 0;
        
        foreach ($posts as $post) {
            if ($this->canDeletePost($post)) {
                $post->delete();
                $count++;
            }
        }
        
        $this->selectedPosts = [];
        $this->selectAll = false;
        
        $this->notification()->success('Success', "{$count} posts have been deleted.");
    }

    private function bulkFeature()
    {
        $this->authorize('posts.feature');
        
        $posts = Post::whereIn('id', $this->selectedPosts)->get();
        $count = 0;
        
        foreach ($posts as $post) {
            if ($this->canUpdatePost($post)) {
                $post->update(['is_featured' => true]);
                $count++;
            }
        }
        
        $this->selectedPosts = [];
        $this->selectAll = false;
        
        $this->notification()->success('Success', "{$count} posts have been featured.");
    }

    private function bulkUnfeature()
    {
        $this->authorize('posts.feature');
        
        $posts = Post::whereIn('id', $this->selectedPosts)->get();
        $count = 0;
        
        foreach ($posts as $post) {
            if ($this->canUpdatePost($post)) {
                $post->update(['is_featured' => false]);
                $count++;
            }
        }
        
        $this->selectedPosts = [];
        $this->selectAll = false;
        
        $this->notification()->success('Success', "{$count} posts have been unfeatured.");
    }

    private function canViewAllPosts(): bool
    {
        return auth()->user()->can('posts.view-all');
    }

    private function canViewPost(Post $post): bool
    {
        return $this->canViewAllPosts() || 
               (auth()->user()->can('posts.view-own') && $post->user_id === auth()->id());
    }

    private function canUpdatePost(Post $post): bool
    {
        return auth()->user()->can('posts.update-all') || 
               (auth()->user()->can('posts.update-own') && $post->user_id === auth()->id());
    }

    private function canDeletePost(Post $post): bool
    {
        return auth()->user()->can('posts.delete-all') || 
               (auth()->user()->can('posts.delete-own') && $post->user_id === auth()->id());
    }

    public function render()
    {
        return view('livewire.admin.blog.posts.post-index', [
            'posts' => $this->posts,
            'categories' => $this->categories,
            'authors' => $this->authors,
            'statusOptions' => $this->statusOptions,
        ])->layout('layouts.admin');
    }
}
