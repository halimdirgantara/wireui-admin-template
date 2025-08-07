<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use WireUi\Traits\WireUiActions;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserCreate extends Component
{
    use WireUiActions;

    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $status = 'active';
    public $selectedRoles = [];
    public $avatar = '';

    public function mount()
    {
        // Check permission
        if (!auth()->user()->can('users.create')) {
            abort(403);
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'status' => 'required|boolean',
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
            'password.required' => 'The password field is required.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'status' => $this->status,
                'avatar' => $this->avatar ?: null,
                'email_verified_at' => now(), // Auto-verify created users
            ]);

            // Assign roles if selected
            if (!empty($this->selectedRoles)) {
                $roles = Role::whereIn('id', $this->selectedRoles)->get();
                $user->assignRole($roles);
            }

            // Log the activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Created user: {$user->name} ({$user->email})");

            $this->notification()->send([
                'icon' => 'success',
                'title' => 'User Createed!',
                'description' => 'User created successfully.',
            ]);

            return redirect()->route('admin.users.index');

        } catch (\Exception $e) {
            $description = 'Failed to create user. Please try again.';
            if (app()->isLocal()) {
                $description .= ' Error: ' . $e->getMessage();
            }
            $this->notification()->send([
                'icon' => 'error',
                'title' => 'Failed!',
                'description' => $description,
            ]);
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
        return view('livewire.admin.users.user-create', [
            'roles' => $this->roles,
        ]);
    }
}
