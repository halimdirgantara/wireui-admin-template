<?php

namespace App\Livewire\Admin\Components;

use App\Models\User;
use App\Models\Activity;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Collection;

class GlobalSearch extends Component
{
    public string $search = '';
    public bool $showResults = false;
    public array $results = [];
    public bool $isLoading = false;
    
    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->isLoading = true;
        
        if (strlen($this->search) >= 2) {
            $this->performSearch();
            $this->showResults = true;
        } else {
            $this->results = [];
            $this->showResults = false;
        }
        
        $this->isLoading = false;
    }

    private function performSearch()
    {
        $this->results = [];
        
        // Search users
        if (auth()->user()->can('users.view')) {
            $users = User::multiColumnSearch($this->search)
                ->with('roles')
                ->limit(5)
                ->get();
            
            $this->results['users'] = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'type' => 'user',
                    'title' => $user->name,
                    'subtitle' => $user->email,
                    'avatar' => $user->avatar_url,
                    'meta' => $user->roles->pluck('name')->implode(', '),
                    'url' => route('admin.users.show', $user),
                    'highlighted_title' => (new User())->highlightSearchResults($user->name, $this->search),
                    'highlighted_subtitle' => (new User())->highlightSearchResults($user->email, $this->search),
                ];
            })->toArray();
        }

        // Search activities
        if (auth()->user()->can('activity-logs.view')) {
            $activities = Activity::multiColumnSearch($this->search)
                ->with('user')
                ->latest()
                ->limit(5)
                ->get();
            
            $this->results['activities'] = $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => 'activity',
                    'title' => $activity->type,
                    'subtitle' => $activity->description,
                    'avatar' => $activity->user?->avatar_url,
                    'meta' => $activity->created_at->diffForHumans(),
                    'url' => route('admin.activity-logs.index', ['search' => $this->search]),
                    'highlighted_title' => (new Activity())->highlightSearchResults($activity->type, $this->search),
                    'highlighted_subtitle' => (new Activity())->highlightSearchResults($activity->description, $this->search),
                ];
            })->toArray();
        }
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->results = [];
        $this->showResults = false;
    }

    public function hideResults()
    {
        $this->showResults = false;
    }

    #[On('global-search-focus')]
    public function onFocus()
    {
        if (!empty($this->search)) {
            $this->showResults = true;
        }
    }

    public function getTotalResultsProperty()
    {
        return collect($this->results)->flatten(1)->count();
    }

    public function render()
    {
        return view('livewire.admin.components.global-search');
    }
}