<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::firstOrCreate(['name' => 'attendee']);

        $this->user = User::factory()->create([
            'email' => 'wasim@chaiCode.com',
            'password' => Hash::make('jingerchai@200'),
            'role_id' => $this->role->id,
            'status' => 'active',
        ]);
    }

    

    
    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'wasim@chaiCode.com',
            'password' => 'jingerchai@200',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
                'user' => $this->role->name,
            ]);

      
    }

   
    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'wasim@chaiCode.com',
            'password' => 'sulaimaani@422',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid email or password',
            ]);

        $this->assertGuest();
    }

    
    public function test_user_login_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'wasim@ladybird.com',
            'password' => 'jingerchai@200',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid email or password',
            ]);

        $this->assertGuest();
    }

    
    public function test_login_validates_missing_email_and_password(): void
    {
        $response = $this->postJson('/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

   
    public function test_login_fails_invalid_email_format(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'wasim@',
            'password' => 'jingerchai@200',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

   
    public function test_login_validates_minimum_password_length(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'john@example.com',
            'password' => '12345',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

   


}
