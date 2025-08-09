<?php

namespace App\Livewire\Admin\Components;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Notifications\DatabaseNotification;

class NotificationCenter extends Component
{
    use WithPagination;

    public bool $showDropdown = false;
    public string $filter = 'unread';
    public bool $autoRefresh = true;
    public int $refreshInterval = 30; // seconds

    public array $availableFilters = [
        'all' => 'All Notifications',
        'unread' => 'Unread Only',
        'read' => 'Read Only',
    ];

    public function mount()
    {
        // Auto-refresh every 30 seconds if enabled
        if ($this->autoRefresh) {
            $this->dispatch('start-notification-refresh', interval: $this->refreshInterval * 1000);
        }
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        
        if ($this->showDropdown) {
            $this->dispatch('notification-dropdown-opened');
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notification-marked-read', notificationId: $notificationId);
            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'Notification marked as read.',
            ]);
        }
    }

    public function markAsUnread($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->update(['read_at' => null]);
            $this->dispatch('notification-marked-unread', notificationId: $notificationId);
            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'Notification marked as unread.',
            ]);
        }
    }

    public function deleteNotification($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->delete();
            $this->dispatch('notification-deleted', notificationId: $notificationId);
            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'Notification deleted.',
            ]);
        }
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->dispatch('all-notifications-marked-read');
        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function deleteAllRead()
    {
        auth()->user()->readNotifications()->delete();
        $this->dispatch('read-notifications-deleted');
        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'All read notifications deleted.',
        ]);
    }

    #[On('refresh-notifications')]
    public function refresh()
    {
        // This will trigger a re-render
        $this->dispatch('notifications-refreshed');
    }

    public function getNotificationsProperty()
    {
        $query = auth()->user()->notifications();

        // Apply filter
        switch ($this->filter) {
            case 'unread':
                $query->whereNull('read_at');
                break;
            case 'read':
                $query->whereNotNull('read_at');
                break;
            // 'all' doesn't need additional filtering
        }

        return $query->latest()->paginate(10);
    }

    public function getUnreadCountProperty()
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function getNotificationIcon($notification)
    {
        $type = $notification->data['type'] ?? 'default';
        
        $icons = [
            'welcome' => 'hand-raised',
            'profile_incomplete' => 'exclamation-triangle',
            'security_alert' => 'shield-exclamation',
            'system_update' => 'cog-6-tooth',
            'password_expiry' => 'key',
            'account_verified' => 'check-badge',
            'user_created' => 'user-plus',
            'role_assigned' => 'identification',
            'login_alert' => 'arrow-right-on-rectangle',
        ];

        return $icons[$type] ?? 'bell';
    }

    public function getNotificationColor($notification)
    {
        $type = $notification->data['type'] ?? 'default';
        
        $colors = [
            'welcome' => 'blue',
            'profile_incomplete' => 'yellow',
            'security_alert' => 'red',
            'system_update' => 'purple',
            'password_expiry' => 'orange',
            'account_verified' => 'green',
            'user_created' => 'green',
            'role_assigned' => 'blue',
            'login_alert' => 'yellow',
        ];

        return $colors[$type] ?? 'gray';
    }

    public function getNotificationUrl($notification)
    {
        $type = $notification->data['type'] ?? 'default';
        
        // Define default URLs based on notification type
        $urls = [
            'user_created' => route('admin.users.index'),
            'role_assigned' => route('admin.roles.index'),
            'security_alert' => route('admin.activity-logs.index'),
            'system_update' => route('admin.dashboard'),
        ];

        return $notification->data['url'] ?? $urls[$type] ?? route('admin.dashboard');
    }

    public function render()
    {
        return view('livewire.admin.components.notification-center', [
            'notifications' => $this->notifications,
            'unreadCount' => $this->unreadCount,
        ]);
    }
}