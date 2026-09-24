<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Fixed/reference data
        $this->call([
            RoleSeeder::class,
            // CategorySeeder::class,
        ]);

        // // Users
        // $admin = User::factory()
        //     ->admin()
        //     ->create();

        // $organizers = User::factory()
        //     ->count(5)
        //     ->organizer()
        //     ->create();

        // User::factory()
        //     ->count(20)
        //     ->attendee()
        //     ->create();

        // // Events
        // foreach ($organizers as $organizer) {

        //     Event::factory()
        //         ->count(3)
        //         ->for($organizer, 'organizer')
        //         ->create();
        // }

    }
}
