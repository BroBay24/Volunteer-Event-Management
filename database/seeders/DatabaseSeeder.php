<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Event::create([
             'title' => 'Volunteer Beach Cleanup',
             'description' => 'Cleaning up the beach for a better environment.',
             'event_date' => now()->addDays(7),
        ]);

         Event::create([
             'title' => 'Tree Planting Day',
             'description' => 'Join us to plant 1000 trees.',
             'event_date' => now()->addDays(14),
        ]);
    }
}
