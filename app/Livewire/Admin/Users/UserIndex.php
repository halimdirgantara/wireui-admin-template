<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use WireUi\Traits\WireUiActions;
use Spatie\Permission\Models\Role;


class UserIndex extends Component
{
    use WithPagination, WireUiActions;

    #[Url(keep: true)]
    public $search = '';
    
    #[Url(keep: true)]
    public $sortField = 'created_at';
    
    #[Url(keep: true)]
    public $sortDirection = 'desc';
    
    #[Url(keep: true)]
    public $statusFilter = 'all';
    
    #[Url(keep: true)]
    public $roleFilter = 'all';
    
    #[Url(keep: true)]
    public $perPage = 10;

    public $selectedUsers = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => 'all'],
        'roleFilter' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        // Check permission
        if (!auth()->user()->can('users.view')) {
            abort(403);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->roleFilter = 'all';
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function deleteUser($userId)
    {
        // Check permission
        if (!auth()->user()->can('users.delete')) {
            $this->notification()->error('Access Denied', 'You do not have permission to delete users.');
            return;
        }

        $user = User::find($userId);
        
        if (!$user) {
            $this->notification()->error('Error', 'User not found.');
            return;
        }

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            $this->notification()->error('Error', 'You cannot delete your own account.');
            return;
        }

        // Prevent deleting super admin if current user is not super admin
        if ($user->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin')) {
            $this->notification()->error('Error', 'You cannot delete a Super Admin.');
            return;
        }

        $user->delete();
        activity()
            ->causedBy(auth()->user())
            ->log("Deleted user: {$user->name} ({$user->email})");

        $this->notification()->success('Success', 'User deleted successfully.');
    }

    public function toggleUserStatus($userId)
    {
        // Check permission
        if (!auth()->user()->can('users.update')) {
            $this->notification()->error('Access Denied', 'You do not have permission to update users.');
            return;
        }

        $user = User::find($userId);
        
        if (!$user) {
            $this->notification()->error('Error', 'User not found.');
            return;
        }

        // Prevent deactivating self
        if ($user->id === auth()->id()) {
            $this->notification()->error('Error', 'You cannot deactivate your own account.');
            return;
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("User {$status}: {$user->name}");

        $this->notification()->success('Success', "User {$status} successfully.");
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = $this->users->pluck('id')->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function updatedSelectedUsers()
    {
        $this->selectAll = count($this->selectedUsers) === $this->users->count();
    }

    public function bulkDelete()
    {
        // Check permission
        if (!auth()->user()->can('users.delete')) {
            $this->notification()->error('Access Denied', 'You do not have permission to delete users.');
            return;
        }

        if (empty($this->selectedUsers)) {
            $this->notification()->error('Error', 'Please select users to delete.');
            return;
        }

        $users = User::whereIn('id', $this->selectedUsers)->get();
        $deletedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($users as $user) {
            // Skip if trying to delete self
            if ($user->id === auth()->id()) {
                $skippedCount++;
                $errors[] = "Cannot delete your own account";
                continue;
            }

            // Skip if trying to delete super admin without being super admin
            if ($user->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin')) {
                $skippedCount++;
                $errors[] = "Cannot delete Super Admin: {$user->name}";
                continue;
            }

            $user->delete();
            activity()
                ->causedBy(auth()->user())
                ->log("Bulk deleted user: {$user->name} ({$user->email})");
            $deletedCount++;
        }

        $this->selectedUsers = [];
        $this->selectAll = false;

        if ($deletedCount > 0) {
            $this->notification()->success('Success', "Successfully deleted {$deletedCount} user(s).");
        }

        if ($skippedCount > 0) {
            $this->notification()->warning('Warning', "Skipped {$skippedCount} user(s): " . implode(', ', array_unique($errors)));
        }
    }

    public function bulkActivate()
    {
        // Check permission
        if (!auth()->user()->can('users.update')) {
            $this->notification()->error('Access Denied', 'You do not have permission to update users.');
            return;
        }

        if (empty($this->selectedUsers)) {
            $this->notification()->error('Error', 'Please select users to activate.');
            return;
        }

        $count = User::whereIn('id', $this->selectedUsers)
                    ->where('id', '!=', auth()->id()) // Don't update self
                    ->update(['is_active' => true]);

        if ($count > 0) {
            activity()
                ->causedBy(auth()->user())
                ->log("Bulk activated {$count} user(s)");

            $this->notification()->success('Success', "Successfully activated {$count} user(s).");
        }

        $this->selectedUsers = [];
        $this->selectAll = false;
    }

    public function bulkDeactivate()
    {
        // Check permission
        if (!auth()->user()->can('users.update')) {
            $this->notification()->error('Access Denied', 'You do not have permission to update users.');
            return;
        }

        if (empty($this->selectedUsers)) {
            $this->notification()->error('Error', 'Please select users to deactivate.');
            return;
        }

        $count = User::whereIn('id', $this->selectedUsers)
                    ->where('id', '!=', auth()->id()) // Don't update self
                    ->update(['is_active' => false]);

        if ($count > 0) {
            activity()
                ->causedBy(auth()->user())
                ->log("Bulk deactivated {$count} user(s)");

            $this->notification()->success('Success', "Successfully deactivated {$count} user(s).");
        }

        $this->selectedUsers = [];
        $this->selectAll = false;
    }

    public function getUsersProperty()
    {
        return User::query()
            ->with(['roles'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('is_active', true);
                } else if ($this->statusFilter === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->when($this->roleFilter !== 'all', function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getRolesProperty()
    {
        return Role::orderBy('name')->get();
    }
    
    #[Layout('layouts.admin')] 
    public function render()
    {
        return view('livewire.admin.users.user-index', [
            'users' => $this->users,
            'roles' => $this->roles,
        ]);
    }
}
