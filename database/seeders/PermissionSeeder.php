<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard permissions
            'dashboard.view',
            'dashboard.analytics',
            
            // User management permissions
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.restore',
            'users.force-delete',
            'users.export',
            'users.import',
            
            // Role management permissions
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.assign',
            
            // Permission management
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
            
            // Activity log permissions
            'activity-logs.view',
            'activity-logs.delete',
            'activity-logs.export',
            
            // Blog Posts permissions
            'posts.view',
            'posts.view-all',
            'posts.view-own',
            'posts.create',
            'posts.update',
            'posts.update-all',
            'posts.update-own',
            'posts.delete',
            'posts.delete-all',
            'posts.delete-own',
            'posts.publish',
            'posts.unpublish',
            'posts.schedule',
            'posts.feature',
            'posts.export',
            'posts.import',
            
            // Blog Categories permissions
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'categories.reorder',
            
            // Blog Tags permissions
            'tags.view',
            'tags.create',
            'tags.update',
            'tags.delete',
            'tags.merge',
            
            // Blog Analytics permissions
            'blog-analytics.view',
            'blog-analytics.advanced',
            'blog-analytics.export',
            
            // Blog SEO permissions
            'blog-seo.manage',
            'blog-seo.meta-tags',
            'blog-seo.sitemap',
            'blog-seo.redirects',
            
            // System settings
            'settings.view',
            'settings.update',
            'settings.system',
            
            // Profile management
            'profile.view',
            'profile.update',
            'profile.change-password',
            
            // Reports and analytics
            'reports.view',
            'reports.create',
            'reports.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin - Most permissions except system settings
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions([
            'dashboard.view',
            'dashboard.analytics',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.export',
            'users.import',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.assign',
            'permissions.view',
            'activity-logs.view',
            'activity-logs.export',
            // Blog permissions for Admin
            'posts.view',
            'posts.view-all',
            'posts.create',
            'posts.update',
            'posts.update-all',
            'posts.delete',
            'posts.delete-all',
            'posts.publish',
            'posts.unpublish',
            'posts.schedule',
            'posts.feature',
            'posts.export',
            'posts.import',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'categories.reorder',
            'tags.view',
            'tags.create',
            'tags.update',
            'tags.delete',
            'tags.merge',
            'blog-analytics.view',
            'blog-analytics.advanced',
            'blog-analytics.export',
            'blog-seo.manage',
            'blog-seo.meta-tags',
            'blog-seo.sitemap',
            'blog-seo.redirects',
            'profile.view',
            'profile.update',
            'profile.change-password',
            'reports.view',
            'reports.create',
            'reports.export',
        ]);

        // Editor - Content management focused
        $editor = Role::firstOrCreate(['name' => 'Editor']);
        $editor->syncPermissions([
            'dashboard.view',
            'users.view',
            'users.update',
            'users.export',
            'activity-logs.view',
            // Blog permissions for Editor
            'posts.view',
            'posts.view-own',
            'posts.create',
            'posts.update-own',
            'posts.delete-own',
            'posts.publish',
            'posts.schedule',
            'posts.export',
            'categories.view',
            'categories.create',
            'categories.update',
            'tags.view',
            'tags.create',
            'tags.update',
            'blog-analytics.view',
            'blog-seo.meta-tags',
            'profile.view',
            'profile.update',
            'profile.change-password',
            'reports.view',
            'reports.create',
        ]);

        // Blog Author - Focused on content creation
        $blogAuthor = Role::firstOrCreate(['name' => 'Blog Author']);
        $blogAuthor->syncPermissions([
            'dashboard.view',
            // Blog permissions for Author (own content only)
            'posts.view',
            'posts.view-own',
            'posts.create',
            'posts.update-own',
            'posts.delete-own',
            'posts.schedule',
            'categories.view',
            'tags.view',
            'tags.create',
            'blog-analytics.view',
            'blog-seo.meta-tags',
            'profile.view',
            'profile.update',
            'profile.change-password',
        ]);

        // Viewer - Read-only access
        $viewer = Role::firstOrCreate(['name' => 'Viewer']);
        $viewer->syncPermissions([
            'dashboard.view',
            'users.view',
            'roles.view',
            'permissions.view',
            'activity-logs.view',
            // Blog permissions for Viewer (read-only)
            'posts.view',
            'posts.view-own',
            'categories.view',
            'tags.view',
            'blog-analytics.view',
            'profile.view',
            'profile.update',
            'profile.change-password',
            'reports.view',
        ]);

        // Create default Super Admin user
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@wireui-admin.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $superAdminUser->assignRole($superAdmin);

        $this->command->info('Permissions and roles created successfully!');
        $this->command->info('Super Admin user created:');
        $this->command->info('Email: admin@wireui-admin.local');
        $this->command->info('Password: password');
    }
}
