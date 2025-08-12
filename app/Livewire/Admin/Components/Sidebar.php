<?php

namespace App\Livewire\Admin\Components;

use Livewire\Component;

class Sidebar extends Component
{
    public function getNavigationItemsProperty()
    {
        return collect([
            [
                'name' => 'Dashboard',
                'route' => 'admin.dashboard',
                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z',
                'permission' => null, // No permission required for dashboard
            ],
            [
                'name' => 'Users',
                'route' => 'admin.users.index',
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
                'permission' => 'users.view',
            ],
            [
                'name' => 'Roles & Permissions',
                'route' => 'admin.roles.index',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'permission' => 'roles.view',
            ],
            [
                'name' => 'Activity Logs',
                'route' => 'admin.activity-logs.index',
                'icon' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                'permission' => 'activity-logs.view',
            ],
            [
                'name' => 'Blog Posts',
                'route' => 'admin.blog.posts.index',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'permission' => 'posts.view',
            ],
            [
                'name' => 'Categories',
                'route' => 'admin.blog.categories.index',
                'icon' => 'M19 11H5m14-7H3a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2z',
                'permission' => 'categories.view',
            ],
            [
                'name' => 'Tags',
                'route' => 'admin.blog.tags.index',
                'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                'permission' => 'tags.view',
            ],
            [
                'name' => 'Settings',
                'route' => 'admin.settings.index',
                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'permission' => 'settings.view',
            ],
        ])->filter(function ($item) {
            // If no permission required, show the item
            if (!$item['permission']) {
                return true;
            }
            
            // Check if user has permission (only if user is authenticated)
            if (auth()->check()) {
                return auth()->user()->can($item['permission']);
            }
            
            return false;
        });
    }

    public function render()
    {
        return view('livewire.admin.components.sidebar', [
            'navigationItems' => $this->navigationItems,
        ]);
    }
}
