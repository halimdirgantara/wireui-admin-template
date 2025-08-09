<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use WireUi\Traits\WireUiActions;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleIndex extends Component
{
    use WithPagination, WireUiActions;

    #[Url(keep: true)]
    public $search = '';
    
    #[Url(keep: true)]
    public $sortField = 'name';
    
    #[Url(keep: true)]
    public $sortDirection = 'asc';
    
    #[Url(keep: true)]
    public $perPage = 10;

    public $selectedRoles = [];
    public $selectAll = false;

    // Modal states
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showPermissionModal = false;

    // Role creation/editing
    public $editingRole = null;
    public $roleName = '';
    public $roleDescription = '';
    public $selectedPermissions = [];

    // Permission creation
    public $permissionName = '';
    public $permissionCategory = 'dashboard';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        // Check permission
        if (!auth()->user()->can('roles.view')) {
            abort(403);
        }
    }

    public function updatedSearch()
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
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function openCreateModal()
    {
        if (!auth()->user()->can('roles.create')) {
            $this->notification()->error('Access Denied', 'You do not have permission to create roles.');
            return;
        }

        $this->resetModal();
        $this->showCreateModal = true;
    }

    public function openEditModal($roleId)
    {
        if (!auth()->user()->can('roles.update')) {
            $this->notification()->error('Access Denied', 'You do not have permission to edit roles.');
            return;
        }

        $role = Role::findOrFail($roleId);
        
        // Prevent editing Super Admin role by non-Super Admin
        if ($role->name === 'Super Admin' && !auth()->user()->hasRole('Super Admin')) {
            $this->notification()->error('Access Denied', 'You cannot edit the Super Admin role.');
            return;
        }

        $this->editingRole = $role;
        $this->roleName = $role->name;
        $this->roleDescription = $role->description ?? '';
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->showEditModal = true;
    }

    public function createRole()
    {
        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name',
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'exists:permissions,id',
        ]);

        try {
            $role = Role::create([
                'name' => $this->roleName,
                'guard_name' => 'web',
            ]);

            if (!empty($this->selectedPermissions)) {
                $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
                $role->givePermissionTo($permissions);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->log("Created role: {$role->name}");

            $this->notification()->success('Success', 'Role created successfully.');
            $this->showCreateModal = false;
            $this->resetModal();

        } catch (\Exception $e) {
            $this->notification()->error('Error', 'Failed to create role. Please try again.');
        }
    }

    public function updateRole()
    {
        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name,' . $this->editingRole->id,
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'exists:permissions,id',
        ]);

        try {
            $this->editingRole->update([
                'name' => $this->roleName,
            ]);

            // Sync permissions
            if (!empty($this->selectedPermissions)) {
                $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
                $this->editingRole->syncPermissions($permissions);
            } else {
                $this->editingRole->syncPermissions([]);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($this->editingRole)
                ->log("Updated role: {$this->editingRole->name}");

            $this->notification()->success('Success', 'Role updated successfully.');
            $this->showEditModal = false;
            $this->resetModal();

        } catch (\Exception $e) {
            $this->notification()->error('Error', 'Failed to update role. Please try again.');
        }
    }

    public function deleteRole($roleId)
    {
        if (!auth()->user()->can('roles.delete')) {
            $this->notification()->error('Access Denied', 'You do not have permission to delete roles.');
            return;
        }

        $role = Role::findOrFail($roleId);

        // Prevent deleting Super Admin role
        if ($role->name === 'Super Admin') {
            $this->notification()->error('Error', 'Cannot delete the Super Admin role.');
            return;
        }

        // Check if role is assigned to users
        $userCount = $role->users()->count();
        if ($userCount > 0) {
            $this->notification()->error('Error', "Cannot delete role. It is assigned to {$userCount} user(s).");
            return;
        }

        try {
            activity()
                ->causedBy(auth()->user())
                ->log("Deleted role: {$role->name}");

            $role->delete();
            $this->notification()->success('Success', 'Role deleted successfully.');

        } catch (\Exception $e) {
            $this->notification()->error('Error', 'Failed to delete role. Please try again.');
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRoles = $this->roles->pluck('id')->toArray();
        } else {
            $this->selectedRoles = [];
        }
    }

    public function updatedSelectedRoles()
    {
        $this->selectAll = count($this->selectedRoles) === $this->roles->count();
    }

    public function bulkDelete()
    {
        if (!auth()->user()->can('roles.delete')) {
            $this->notification()->error('Access Denied', 'You do not have permission to delete roles.');
            return;
        }

        if (empty($this->selectedRoles)) {
            $this->notification()->error('Error', 'Please select roles to delete.');
            return;
        }

        $roles = Role::whereIn('id', $this->selectedRoles)->get();
        $deletedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($roles as $role) {
            // Skip Super Admin role
            if ($role->name === 'Super Admin') {
                $skippedCount++;
                $errors[] = "Cannot delete Super Admin role";
                continue;
            }

            // Skip roles assigned to users
            $userCount = $role->users()->count();
            if ($userCount > 0) {
                $skippedCount++;
                $errors[] = "Role '{$role->name}' assigned to {$userCount} user(s)";
                continue;
            }

            $role->delete();
            activity()
                ->causedBy(auth()->user())
                ->log("Bulk deleted role: {$role->name}");
            $deletedCount++;
        }

        $this->selectedRoles = [];
        $this->selectAll = false;

        if ($deletedCount > 0) {
            $this->notification()->success('Success', "Successfully deleted {$deletedCount} role(s).");
        }

        if ($skippedCount > 0) {
            $this->notification()->warning('Warning', "Skipped {$skippedCount} role(s): " . implode(', ', array_unique($errors)));
        }
    }

    public function openPermissionModal()
    {
        if (!auth()->user()->can('permissions.create')) {
            $this->notification()->error('Access Denied', 'You do not have permission to create permissions.');
            return;
        }

        $this->resetPermissionModal();
        $this->showPermissionModal = true;
    }

    public function createPermission()
    {
        $this->validate([
            'permissionName' => 'required|string|max:255',
            'permissionCategory' => 'required|string|max:50',
        ]);

        // Create the full permission name
        $fullPermissionName = $this->permissionCategory . '.' . $this->permissionName;

        // Check if permission already exists
        if (Permission::where('name', $fullPermissionName)->exists()) {
            $this->notification()->error('Error', 'Permission already exists.');
            return;
        }

        try {
            $permission = Permission::create([
                'name' => $fullPermissionName,
                'guard_name' => 'web',
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($permission)
                ->log("Created permission: {$permission->name}");

            $this->notification()->success('Success', 'Permission created successfully.');
            $this->showPermissionModal = false;
            $this->resetPermissionModal();

        } catch (\Exception $e) {
            $this->notification()->error('Error', 'Failed to create permission. Please try again.');
        }
    }

    private function resetPermissionModal()
    {
        $this->permissionName = '';
        $this->permissionCategory = 'dashboard';
    }

    private function resetModal()
    {
        $this->roleName = '';
        $this->roleDescription = '';
        $this->selectedPermissions = [];
        $this->editingRole = null;
    }

    public function getRolesProperty()
    {
        return Role::query()
            ->withCount(['users', 'permissions'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')->get()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.roles.role-index', [
            'roles' => $this->roles,
            'permissions' => $this->permissions,
        ]);
    }
}
