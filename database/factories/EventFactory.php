<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $start = fake()->dateTimeBetween('+1 week', '+3 months');

        return [
            'organizer_id' => null,

            'category_id' => Category::factory(),

            'title' => fake()->sentence(4),

            'description' => fake()->paragraph(),

            'location' => fake()->city(),

            'start_at' => $start,

            'end_at' => (clone $start)->modify('+4 hours'),

            'registration_deadline' => (clone $start)->modify('-2 days'),

            'capacity' => $capacity = fake()->numberBetween(50, 500),
            'available_seats' => $capacity,

            'price' => fake()->randomFloat(2, 0, 2000),

            'status' => 'PUBLISHED',

            'cover_image' => null,

        ];
    }
}
