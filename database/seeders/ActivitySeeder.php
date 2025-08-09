<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users or create some if none exist
        $users = User::all();
        
        if ($users->isEmpty()) {
            // Create some users first
            $users = User::factory(10)->create();
        }

        // Create activities for existing users
        foreach ($users as $user) {
            // Create 5-15 random activities per user
            Activity::factory(
                fake()->numberBetween(5, 15)
            )->create([
                'user_id' => $user->id,
            ]);
        }

        // Create some additional activities with random users
        Activity::factory(50)->create();

        $this->command->info('Created activities for ' . $users->count() . ' users.');
    }
}
