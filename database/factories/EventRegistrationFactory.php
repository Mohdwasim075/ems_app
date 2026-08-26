<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRegistration>
 */
class EventRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
             return [
            'event_id' => Event::factory(),

            'user_id' => User::factory()->attendee(),

            'registration_number' =>
                'REG-' . fake()->unique()->numerify('######'),

            'quantity' => fake()->numberBetween(1, 4),

            'total_price' => fake()->randomFloat(2, 100, 5000),

            'status' => 'CONFIRMED',

            'registered_at' => fake()->dateTimeBetween(
                '-1 month',
                'now'
            ),

            'cancelled_at' => null,
        ];
    }
}
