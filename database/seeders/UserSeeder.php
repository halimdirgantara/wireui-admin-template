<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Create editor user if it doesn't exist
        $editor = User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Editor User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Create regular user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Create inactive user if it doesn't exist
        $inactiveUser = User::firstOrCreate(
            ['email' => 'inactive@example.com'],
            [
                'name' => 'Inactive User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => false,
            ]
        );

        // Assign roles if they exist
        if (Role::where('name', 'admin')->exists() && !$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
        
        if (Role::where('name', 'editor')->exists() && !$editor->hasRole('editor')) {
            $editor->assignRole('editor');
        }
        
        if (Role::where('name', 'user')->exists()) {
            if (!$user->hasRole('user')) {
                $user->assignRole('user');
            }
            if (!$inactiveUser->hasRole('user')) {
                $inactiveUser->assignRole('user');
            }
        }

        // Get current user count
        $existingUserCount = User::count();
        
        // Create additional random users only if we have less than 20 users
        if ($existingUserCount < 20) {
            $usersToCreate = 20 - $existingUserCount;
            User::factory($usersToCreate)->create();
            $this->command->info("Created {$usersToCreate} additional random users.");
        } else {
            $this->command->info('Sufficient users already exist.');
        }

        $this->command->info('Sample users with different roles and statuses are ready.');
    }
}
