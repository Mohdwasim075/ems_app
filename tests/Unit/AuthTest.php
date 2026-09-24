<?php

namespace Tests\Unit;
namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;


class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }
    /**
     * A basic unit test example.
     */
    public function test_login_succeed_with_valid_credentials()
    {


        $attendeeRole = Role::where('name', 'attendee')->firstOrFail();
        //Arrange
        $user = User::factory()->create([
            'email' => 'wasimchai@gmail.com',
            'password' => Hash::make('789456'),
            'role_id' => $attendeeRole->id

        ]);

        //Act
        $response = $this->postJson('/login', [
            'email' => 'wasimchai@gmail.com',
            'password' => '789456'
        ]);



        $response->assertJson([
            'message' => 'Login successful',
        ]);

        // $this->assertAuthenticatedAs($user);
        // Assert
        $this->assertAuthenticatedAs($user);


    }

    public function test_user_with_invalid_credentials_fail()
    {

        $attendeeRole = Role::where('name', 'attendee')->firstOrFail();

        //Arrange
        $user = User::factory()->create([
            'email' => 'wasim@github.com',
            'password' => Hash::make('789456'),
            'role_id' => $attendeeRole->id

        ]);

        //Act
        $response = $this->postJson('/login', [
            'email' => 'wasimchai@gmail.com',
            'password' => '789456'
        ]);


        $response->assertJson([
            'message' => 'Invalid email or password',
        ]);



    }

}