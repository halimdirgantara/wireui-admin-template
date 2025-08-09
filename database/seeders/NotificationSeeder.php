<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $users = User::factory(10)->create();
        }

        $notificationTypes = [
            'welcome' => [
                'title' => 'Welcome to the platform!',
                'message' => 'Thank you for joining us. Get started by exploring the dashboard.',
                'action_url' => '/dashboard',
                'action_text' => 'Go to Dashboard',
                'type' => 'info'
            ],
            'profile_incomplete' => [
                'title' => 'Complete Your Profile',
                'message' => 'Please complete your profile to get the most out of our platform.',
                'action_url' => '/profile',
                'action_text' => 'Complete Profile',
                'type' => 'warning'
            ],
            'security_alert' => [
                'title' => 'New Login Detected',
                'message' => 'We detected a new login to your account from a new device.',
                'action_url' => '/security',
                'action_text' => 'Review Security',
                'type' => 'warning'
            ],
            'system_update' => [
                'title' => 'System Update Available',
                'message' => 'A new system update is available with improved features and security.',
                'action_url' => '/updates',
                'action_text' => 'Learn More',
                'type' => 'info'
            ],
            'password_expiry' => [
                'title' => 'Password Expiry Warning',
                'message' => 'Your password will expire in 7 days. Please update it to maintain security.',
                'action_url' => '/password/change',
                'action_text' => 'Change Password',
                'type' => 'warning'
            ],
            'account_verified' => [
                'title' => 'Account Verified Successfully',
                'message' => 'Your account has been verified. You now have access to all features.',
                'action_url' => '/dashboard',
                'action_text' => 'Explore Features',
                'type' => 'success'
            ],
        ];

        foreach ($users as $user) {
            // Create 3-8 notifications per user
            $notificationCount = fake()->numberBetween(3, 8);
            
            for ($i = 0; $i < $notificationCount; $i++) {
                $notificationType = fake()->randomKey($notificationTypes);
                $notificationData = $notificationTypes[$notificationType];
                
                $readAt = fake()->boolean(60) ? fake()->dateTimeBetween('-7 days', 'now') : null;
                
                DB::table('notifications')->insert([
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\' . Str::studly($notificationType) . 'Notification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $user->id,
                    'data' => json_encode($notificationData),
                    'read_at' => $readAt,
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                    'updated_at' => now(),
                ]);
            }
        }

        $totalNotifications = DB::table('notifications')->count();
        $this->command->info("Created {$totalNotifications} notifications for {$users->count()} users.");
    }
}
