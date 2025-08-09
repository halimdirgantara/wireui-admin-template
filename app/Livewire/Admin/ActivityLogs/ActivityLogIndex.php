<?php

namespace App\Livewire\Admin\ActivityLogs;

use App\Models\Activity;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class ActivityLogIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $logName = '';
    
    #[Url]
    public string $causerType = '';
    
    #[Url]
    public string $subjectType = '';
    
    #[Url]
    public string $dateFrom = '';
    
    #[Url]
    public string $dateTo = '';
    
    #[Url]
    public string $sortBy = 'created_at';
    
    #[Url]
    public string $sortDirection = 'desc';
    
    #[Url]
    public int $perPage = 25;

    public array $perPageOptions = [10, 25, 50, 100];

    protected $queryString = [
        'search' => ['except' => ''],
        'logName' => ['except' => ''],
        'causerType' => ['except' => ''],
        'subjectType' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 25],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        if (!auth()->user()->can('activity-logs.view')) {
            abort(403);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedLogName()
    {
        $this->resetPage();
    }

    public function updatedCauserType()
    {
        $this->resetPage();
    }

    public function updatedSubjectType()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset([
            'search', 'logName', 'causerType', 'subjectType', 
            'dateFrom', 'dateTo', 'sortBy', 'sortDirection'
        ]);
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function exportActivities($format = 'csv')
    {
        // This would implement export functionality
        $this->dispatch('show-notification', [
            'type' => 'info',
            'message' => 'Export functionality will be implemented soon.',
        ]);
    }

    public function getActivitiesProperty()
    {
        // First check if we have activities in our custom table
        $hasCustomActivities = Activity::exists();
        
        if ($hasCustomActivities) {
            return $this->getCustomActivities();
        } else {
            return $this->getSpatieActivities();
        }
    }

    private function getCustomActivities()
    {
        $query = Activity::with('user');

        // Apply search
        if ($this->search) {
            $query->multiColumnSearch($this->search);
        }

        // Apply activity type filter
        if ($this->logName) {
            $query->where('type', $this->logName);
        }

        // Apply date filters
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

    private function getSpatieActivities()
    {
        $query = SpatieActivity::with(['causer', 'subject']);

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('log_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhereHasMorph('causer', ['App\Models\User'], function ($q) {
                      $q->where('name', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('email', 'LIKE', '%' . $this->search . '%');
                  });
            });
        }

        // Apply filters
        if ($this->logName) {
            $query->where('log_name', $this->logName);
        }

        if ($this->causerType) {
            $query->where('causer_type', $this->causerType);
        }

        if ($this->subjectType) {
            $query->where('subject_type', $this->subjectType);
        }

        // Apply date filters
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

    public function getLogNamesProperty()
    {
        // Get available log names from both tables
        $customLogNames = Activity::distinct()->pluck('type')->filter();
        $spatieLogNames = SpatieActivity::distinct()->pluck('log_name')->filter();
        
        return $customLogNames->merge($spatieLogNames)->unique()->sort();
    }

    public function getCauserTypesProperty()
    {
        return SpatieActivity::distinct()
            ->whereNotNull('causer_type')
            ->pluck('causer_type')
            ->map(function ($type) {
                return class_basename($type);
            })
            ->unique()
            ->sort();
    }

    public function getSubjectTypesProperty()
    {
        return SpatieActivity::distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(function ($type) {
                return class_basename($type);
            })
            ->unique()
            ->sort();
    }

    public function getHasCustomActivitiesProperty()
    {
        return Activity::exists();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.activity-logs.activity-log-index', [
            'activities' => $this->activities,
            'logNames' => $this->logNames,
            'causerTypes' => $this->causerTypes,
            'subjectTypes' => $this->subjectTypes,
            'hasCustomActivities' => $this->hasCustomActivities,
        ]);
    }
}