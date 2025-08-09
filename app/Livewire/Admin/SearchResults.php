<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Activity;
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

    public function getTotalResultsProperty()
    {
        $total = 0;
        
        if (auth()->user()->can('users.view')) {
            $total += User::multiColumnSearch($this->q)->count();
        }
        
        if (auth()->user()->can('activity-logs.view')) {
            $total += Activity::multiColumnSearch($this->q)->count();
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
        
        return $results;
    }

    public function render()
    {
        return view('livewire.admin.search-results', [
            'users' => $this->users,
            'activities' => $this->activities,
            'totalResults' => $this->totalResults,
            'resultsByType' => $this->resultsByType,
        ])->extends('layouts.admin')->section('content');
    }
}