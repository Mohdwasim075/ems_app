<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'name' => fake()->name(),

            'email' => fake()->unique()->safeEmail(),

            'password' => Hash::make('password'),

        'phone_number' => fake()->unique()->numerify('##########'),

            'status' => 'active',
        ];


    }

     public function admin(): static
    {
        return $this->state(function () {
            return [
                'role_id' => Role::where('name', 'ADMIN')->value('id'),
            ];
        });
    }

    public function organizer(): static
    {
        return $this->state(function () {
            return [
                'role_id' => Role::where('name', 'ORGANIZER')->value('id'),
            ];
        });
    }

    public function attendee(): static
    {
        return $this->state(function () {
            return [
                'role_id' => Role::where('name', 'ATTENDEE')->value('id'),
            ];
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
