<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Activity;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class SearchResults extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';
    
    #[Url]
    public string $type = 'all';
    
    #[Url]
    public string $sortBy = 'relevance';
    
    #[Url]
    public string $sortDirection = 'desc';

    public array $availableTypes = [
        'all' => 'All Results',
        'users' => 'Users',
        'activities' => 'Activities',
        'posts' => 'Blog Posts',
        'categories' => 'Categories',
        'tags' => 'Tags',
    ];

    public array $sortOptions = [
        'relevance' => 'Relevance',
        'created_at' => 'Date Created',
        'updated_at' => 'Date Modified',
    ];

    protected $queryString = [
        'q' => ['except' => ''],
        'type' => ['except' => 'all'],
        'sortBy' => ['except' => 'relevance'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        if (empty($this->q)) {
            $this->q = request('q', '');
        }
    }

    public function updatedQ()
    {
        $this->resetPage();
    }

    public function updatedType()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function updatedSortDirection()
    {
        $this->resetPage();
    }

    public function getUsersProperty()
    {
        if ($this->type !== 'all' && $this->type !== 'users') {
            return collect([]);
        }

        if (empty($this->q) || !auth()->user()->can('users.view')) {
            return collect([]);
        }

        $query = User::multiColumnSearch($this->q)->with('roles');

        if ($this->sortBy === 'relevance') {
            // Custom relevance scoring could be implemented here
            $query->orderByRaw("
                CASE 
                    WHEN name LIKE ? THEN 3
                    WHEN email LIKE ? THEN 2
                    ELSE 1
                END DESC
            ", [$this->q . '%', $this->q . '%']);
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $this->type === 'users' 
            ? $query->paginate(10)
            : $query->limit(5)->get();
    }

    public function getActivitiesProperty()
    {
        if ($this->type !== 'all' && $this->type !== 'activities') {
            return collect([]);
        }

        if (empty($this->q) || !auth()->user()->can('activity-logs.view')) {
            return collect([]);
        }

        $query = Activity::multiColumnSearch($this->q)->with('user');

        if ($this->sortBy === 'relevance') {
            // Custom relevance scoring
            $query->orderByRaw("
                CASE 
                    WHEN type LIKE ? THEN 3
                    WHEN description LIKE ? THEN 2
                    ELSE 1
                END DESC
            ", [$this->q . '%', '%' . $this->q . '%']);
        } else {
            $query->orderBy($this->sortBy === 'created_at' ? 'created_at' : 'created_at', $this->sortDirection);
        }

        return $this->type === 'activities' 
            ? $query->paginate(10)
            : $query->limit(5)->get();
    }

    public function getPostsProperty()
    {
        if ($this->type !== 'all' && $this->type !== 'posts') {
            return collect([]);
        }

        if (empty($this->q) || !auth()->user()->can('posts.view')) {
            return collect([]);
        }

        $query = Post::multiColumnSearch($this->q)
            ->with(['user', 'category', 'tags']);

        if ($this->sortBy === 'relevance') {
            // Custom relevance scoring for posts
            $query->orderByRaw("
                CASE 
                    WHEN title LIKE ? THEN 4
                    WHEN excerpt LIKE ? THEN 3
                    WHEN content LIKE ? THEN 2
                    ELSE 1
                END DESC
            ", [$this->q . '%', '%' . $this->q . '%', '%' . $this->q . '%']);
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $this->type === 'posts' 
            ? $query->paginate(10)
            : $query->limit(5)->get();
    }

    public function getCategoriesProperty()
    {
        if ($this->type !== 'all' && $this->type !== 'categories') {
            return collect([]);
        }

        if (empty($this->q) || !auth()->user()->can('categories.view')) {
            return collect([]);
        }

        $query = Category::multiColumnSearch($this->q)
            ->withCount('posts');

        if ($this->sortBy === 'relevance') {
            $query->orderByRaw("
                CASE 
                    WHEN name LIKE ? THEN 3
                    WHEN description LIKE ? THEN 2
                    ELSE 1
                END DESC
            ", [$this->q . '%', '%' . $this->q . '%']);
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $this->type === 'categories' 
            ? $query->paginate(10)
            : $query->limit(5)->get();
    }

    public function getTagsProperty()
    {
        if ($this->type !== 'all' && $this->type !== 'tags') {
            return collect([]);
        }

        if (empty($this->q) || !auth()->user()->can('tags.view')) {
            return collect([]);
        }

        $query = Tag::multiColumnSearch($this->q)
            ->withPostCounts();

        if ($this->sortBy === 'relevance') {
            $query->orderByRaw("
                CASE 
                    WHEN name LIKE ? THEN 3
                    WHEN description LIKE ? THEN 2
                    ELSE 1
                END DESC
            ", [$this->q . '%', '%' . $this->q . '%']);
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $this->type === 'tags' 
            ? $query->paginate(10)
            : $query->limit(5)->get();
    }

    public function getTotalResultsProperty()
    {
        $total = 0;
        
        if (auth()->user()->can('users.view')) {
            $total += User::multiColumnSearch($this->q)->count();
        }
        
        if (auth()->user()->can('activity-logs.view')) {
            $total += Activity::multiColumnSearch($this->q)->count();
        }
        
        if (auth()->user()->can('posts.view')) {
            $total += Post::multiColumnSearch($this->q)->count();
        }
        
        if (auth()->user()->can('categories.view')) {
            $total += Category::multiColumnSearch($this->q)->count();
        }
        
        if (auth()->user()->can('tags.view')) {
            $total += Tag::multiColumnSearch($this->q)->count();
        }
        
        return $total;
    }

    public function getResultsByTypeProperty()
    {
        $results = [];
        
        if (auth()->user()->can('users.view')) {
            $results['users'] = User::multiColumnSearch($this->q)->count();
        }
        
        if (auth()->user()->can('activity-logs.view')) {
            $results['activities'] = Activity::multiColumnSearch($this->q)->count();
        }
        
        if (auth()->user()->can('posts.view')) {
            $results['posts'] = Post::multiColumnSearch($this->q)->count();
        }
        
        if (auth()->user()->can('categories.view')) {
            $results['categories'] = Category::multiColumnSearch($this->q)->count();
        }
        
        if (auth()->user()->can('tags.view')) {
            $results['tags'] = Tag::multiColumnSearch($this->q)->count();
        }
        
        return $results;
    }

    public function render()
    {
        return view('livewire.admin.search-results', [
            'users' => $this->users,
            'activities' => $this->activities,
            'posts' => $this->posts,
            'categories' => $this->categories,
            'tags' => $this->tags,
            'totalResults' => $this->totalResults,
            'resultsByType' => $this->resultsByType,
        ])->extends('layouts.admin')->section('content');
    }
}