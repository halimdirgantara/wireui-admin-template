<?php

namespace App\Livewire\Admin\Components;

use App\Models\Activity;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class ActivityFeed extends Component
{
    use WithPagination;

    public string $filter = 'all';
    public string $timeFilter = 'all';
    public string $search = '';
    public bool $autoRefresh = false;
    public int $refreshInterval = 30; // seconds
    public bool $showFilters = true;
    public int $perPage = 10;

    public array $availableFilters = [
        'all' => 'All Activities',
        'user' => 'User Activities',
        'system' => 'System Activities',
        'login' => 'Login Events',
        'profile_update' => 'Profile Updates',
        'password_change' => 'Password Changes',
    ];

    public array $timeFilters = [
        'all' => 'All Time',
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
    ];

    protected $queryString = [
        'filter' => ['except' => 'all'],
        'timeFilter' => ['except' => 'all'],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        if (!auth()->user()->can('activity-logs.view')) {
            abort(403);
        }
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function updatedTimeFilter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedAutoRefresh()
    {
        if ($this->autoRefresh) {
            $this->dispatch('start-auto-refresh', interval: $this->refreshInterval * 1000);
        } else {
            $this->dispatch('stop-auto-refresh');
        }
    }

    #[On('refresh-activity-feed')]
    public function refresh()
    {
        // This will trigger a re-render
        $this->dispatch('activity-feed-refreshed');
    }

    public function clearFilters()
    {
        $this->reset(['filter', 'timeFilter', 'search']);
        $this->resetPage();
    }

    public function getActivitiesProperty()
    {
        $query = $this->buildQuery();
        
        return $query->paginate($this->perPage);
    }

    public function getSpatieActivitiesProperty()
    {
        $query = SpatieActivity::with(['causer', 'subject'])->latest();

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('log_name', 'LIKE', '%' . $this->search . '%');
            });
        }

        if ($this->filter !== 'all') {
            $query->where('log_name', $this->filter);
        }

        // Apply time filters
        switch ($this->timeFilter) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        return $query->paginate($this->perPage);
    }

    private function buildQuery()
    {
        // First try our custom Activity model
        $query = Activity::with('user')->latest();

        // Apply search
        if ($this->search) {
            $query->multiColumnSearch($this->search);
        }

        // Apply activity type filter
        if ($this->filter !== 'all') {
            $query->where('type', $this->filter);
        }

        // Apply time filters
        switch ($this->timeFilter) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        return $query;
    }

    public function getActivityIcon($activity)
    {
        $icons = [
            'login' => 'arrow-right-on-rectangle',
            'logout' => 'arrow-left-on-rectangle',
            'profile_update' => 'user',
            'password_change' => 'key',
            'email_change' => 'envelope',
            'avatar_update' => 'photo',
            'created' => 'plus-circle',
            'updated' => 'pencil-square',
            'deleted' => 'trash',
        ];

        $type = is_array($activity) ? $activity['type'] ?? 'default' : $activity->type;
        return $icons[$type] ?? 'clock';
    }

    public function getActivityColor($activity)
    {
        $colors = [
            'login' => 'green',
            'logout' => 'gray',
            'profile_update' => 'blue',
            'password_change' => 'yellow',
            'email_change' => 'purple',
            'avatar_update' => 'indigo',
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
        ];

        $type = is_array($activity) ? $activity['type'] ?? 'default' : $activity->type;
        return $colors[$type] ?? 'gray';
    }

    public function render()
    {
        // Check if we have activities in our custom table, otherwise fall back to Spatie
        $hasCustomActivities = Activity::exists();
        
        if ($hasCustomActivities) {
            $activities = $this->activities;
        } else {
            $activities = $this->spatieActivities;
        }

        return view('livewire.admin.components.activity-feed', [
            'activities' => $activities,
            'hasCustomActivities' => $hasCustomActivities,
        ]);
    }
}