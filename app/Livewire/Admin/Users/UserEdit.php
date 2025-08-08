<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use WireUi\Traits\WireUiActions;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserEdit extends Component
{
    use WireUiActions;

    public User $user;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $is_active = true;
    public $selectedRoles = [];
    public $avatar = '';

    public function mount(User $user)
    {
        // Check permission
        if (!auth()->user()->can('users.update')) {
            abort(403);
        }

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_active = $user->is_active ?? true;
        $this->avatar = $user->avatar ?? '';
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_active' => 'required|boolean',
            'selectedRoles' => 'array',
            'selectedRoles.*' => 'exists:roles,id',
            'avatar' => 'nullable|url|max:255',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }

    public function save()
    {
        $this->validate();

        // Prevent editing self status or roles if not Super Admin
        if ($this->user->id === auth()->id()) {
            if ($this->is_active !== $this->user->is_active && !auth()->user()->hasRole('Super Admin')) {
                $this->notification()->error('Error', 'You cannot change your own status.');
                return;
            }
            
            $currentUserRoles = auth()->user()->roles->pluck('id')->toArray();
            if ($this->selectedRoles !== $currentUserRoles && !auth()->user()->hasRole('Super Admin')) {
                $this->notification()->error('Error', 'You cannot change your own roles.');
                return;
            }
        }

        // Prevent non-Super Admin from editing Super Admin
        if ($this->user->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin')) {
            $this->notification()->error('Error', 'You cannot edit a Super Admin user.');
            return;
        }

        try {
            $updateData = [
                'name' => $this->name,
                'email' => $this->email,
                'is_active' => $this->is_active,
                'avatar' => $this->avatar ?: null,
            ];

            // Only update password if provided
            if (!empty($this->password)) {
                $updateData['password'] = Hash::make($this->password);
            }

            $this->user->update($updateData);

            // Sync roles
            $roles = Role::whereIn('id', $this->selectedRoles)->get();
            $this->user->syncRoles($roles);

            // Log the activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($this->user)
                ->log("Updated user: {$this->user->name} ({$this->user->email})");

            $this->notification()->success('Success', 'User updated successfully.');
            
            return redirect()->route('admin.users.index');

        } catch (\Exception $e) {
            $this->notification()->error('Error', 'Failed to update user. Please try again.');
        }
    }

    public function cancel()
    {
        return redirect()->route('admin.users.index');
    }

    public function getRolesProperty()
    {
        return Role::orderBy('name')->get();
    }

    #[Layout('layouts.admin')] 
    public function render()
    {
        return view('livewire.admin.users.user-edit', [
            'roles' => $this->roles,
        ]);
    }
}
