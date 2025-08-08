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
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - All permissions
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - Most permissions except system settings
        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo([
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
            'profile.view',
            'profile.update',
            'profile.change-password',
            'reports.view',
            'reports.create',
            'reports.export',
        ]);

        // Editor - Content management focused
        $editor = Role::create(['name' => 'Editor']);
        $editor->givePermissionTo([
            'dashboard.view',
            'users.view',
            'users.update',
            'users.export',
            'activity-logs.view',
            'profile.view',
            'profile.update',
            'profile.change-password',
            'reports.view',
            'reports.create',
        ]);

        // Viewer - Read-only access
        $viewer = Role::create(['name' => 'Viewer']);
        $viewer->givePermissionTo([
            'dashboard.view',
            'users.view',
            'roles.view',
            'permissions.view',
            'activity-logs.view',
            'profile.view',
            'profile.update',
            'profile.change-password',
            'reports.view',
        ]);

        // Create default Super Admin user
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@wireui-admin.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $superAdminUser->assignRole($superAdmin);

        $this->command->info('Permissions and roles created successfully!');
        $this->command->info('Super Admin user created:');
        $this->command->info('Email: admin@wireui-admin.local');
        $this->command->info('Password: password');
    }
}
