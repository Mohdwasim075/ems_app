<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'admin',
                'organizer',
                'attendee',
            ]),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'admin',
        ]);
    }

    public function attendee(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'attendee',
        ]);
    }

    public function organizer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'organizer',
        ]);
    
    }
}
