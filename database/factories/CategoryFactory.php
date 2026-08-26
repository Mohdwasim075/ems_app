<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
         $start = fake()->dateTimeBetween('+1 week', '+3 months');

    return [
        'title' => fake()->sentence(4),

        'description' => fake()->paragraph(),

        'category_id' => Category::inRandomOrder()->value('id'),

        'location' => fake()->city(),

        'start_at' => $start,

        'end_at' => (clone $start)->modify('+4 hours'),

        'registration_deadline' => (clone $start)->modify('-2 days'),

        'capacity' => fake()->numberBetween(50, 500),

        'price' => fake()->randomFloat(2, 0, 2000),

        'status' => 'PUBLISHED',

        'cover_image' => null,
    ];
    }
}
