<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

   

    public function test_user_can_register_with_valid_credentials(): void
    {
        //arrange

        $userData = [
            'name' => 'wasim Mohammed',
            'email' => 'wasim@chaiCode.com',
            'password' => 'jingerchai@200',
            'password_confirmation' => 'jingerchai@200',
        ];

        //act

        $response = $this->postJson('/register', $userData);

        //assert

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully!',
                'redirect' => url('/login'),
            ]);

        
        $this->assertDatabaseHas('users', [
            'name' => 'wasim Mohammed',
            'email' => 'wasim@chaiCode.com',
        ]);

       
    }

   
    public function test_registration_fails_if_required_fields_are_missing(): void
    {
        $response = $this->postJson('/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

   
    public function test_registration_fails_for_invalid_email(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Jane Doe',
            'email' => 'invalid-email-address',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

   
    public function test_registration_fails_for_email_already_exists(): void
    {
        User::factory()->create([
            'email' => 'wasim@chaiCode.com',
            'role_id' => Role::where('name', 'ATTENDEE')->value('id'),
        ]);

        $response = $this->postJson('/register', [
            'name' => 'wasim official',
            'email' => 'wasim@chaiCode.com',
            'password' => '789456',
            'password_confirmation' => '789456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    

   
    public function test_registration_fails_when_password_not_matches(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'wasim Mohammed',
            'email' => 'wasim@chaiCode.com',
            'password' => '123456',
            'password_confirmation' => '789456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
