<?php

namespace Database\Factories;

use App\Models\EventRegistration;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_registration_id' => EventRegistration::factory(),

            'ticket_number' => 'TKT-'.fake()->unique()->numerify('######'),

            'qr_code' => null,

            'status' => 'VALID',

            'checked_in_at' => null,
        ];
    }
}
