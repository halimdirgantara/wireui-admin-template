<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activityTypes = [
            'login' => 'User logged in',
            'logout' => 'User logged out',
            'profile_update' => 'User updated their profile',
            'password_change' => 'User changed their password',
            'email_change' => 'User changed their email address',
            'avatar_update' => 'User updated their avatar',
            'account_deactivated' => 'User account was deactivated',
            'account_activated' => 'User account was activated',
            'permission_granted' => 'User was granted new permissions',
            'permission_revoked' => 'User permissions were revoked',
        ];

        $type = fake()->randomElement(array_keys($activityTypes));
        $baseDescription = $activityTypes[$type];
        
        // Add some variation to descriptions
        $descriptions = [
            $baseDescription,
            $baseDescription . ' from ' . fake()->ipv4(),
            $baseDescription . ' using ' . fake()->userAgent(),
            $baseDescription . ' successfully',
        ];

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'description' => fake()->randomElement($descriptions),
            'meta' => $this->generateMeta($type),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Generate metadata based on activity type.
     */
    private function generateMeta(string $type): array
    {
        $baseMeta = [
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];

        return match ($type) {
            'login', 'logout' => array_merge($baseMeta, [
                'session_id' => fake()->uuid(),
                'location' => fake()->city() . ', ' . fake()->country(),
            ]),
            'profile_update' => array_merge($baseMeta, [
                'fields_changed' => fake()->randomElements(['name', 'email', 'avatar'], fake()->numberBetween(1, 3)),
            ]),
            'password_change' => array_merge($baseMeta, [
                'strength' => fake()->randomElement(['weak', 'medium', 'strong']),
            ]),
            'permission_granted', 'permission_revoked' => array_merge($baseMeta, [
                'permissions' => fake()->randomElements(['view_users', 'create_users', 'edit_users', 'delete_users'], fake()->numberBetween(1, 2)),
                'granted_by' => fake()->name(),
            ]),
            default => $baseMeta,
        };
    }
}
