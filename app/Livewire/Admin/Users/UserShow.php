<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Spatie\Activitylog\Models\Activity;

class UserShow extends Component
{
    public User $user;

    public function mount(User $user)
    {
        // Check permission
        if (!auth()->user()->can('users.view')) {
            abort(403);
        }

        $this->user = $user;
    }

    public function getRecentActivitiesProperty()
    {
        return Activity::query()
            ->where(function ($query) {
                $query->where('causer_id', $this->user->id)
                      ->orWhere('subject_id', $this->user->id);
            })
            ->with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    #[Layout('layouts.admin')] 
    public function render()
    {
        return view('livewire.admin.users.user-show', [
            'recentActivities' => $this->recentActivities,
        ]);
    }
}
