<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $attendeeUser;
    protected Role $adminRole;
    protected Role $attendeeRole;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->attendeeRole = Role::firstOrCreate(['name' => 'attendee']);

        $this->adminUser = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'status' => 'active',
            'email' => 'admin@chaicode.com',
        ]);

        $this->attendeeUser = User::factory()->create([
            'role_id' => $this->attendeeRole->id,
            'status' => 'active',
            'email' => 'wasimchai@gmail.com',
            'phone_number' => '1234567890',
            'city' => 'Metropolis',
            'state' => 'New York',
            'zip' => '10001',
        ]);

        $this->category = Category::factory()->create();
    }

   
    protected function createEvent(array $overrides = []): Event
    {
        return Event::factory()->create(array_merge([
            'category_id' => $this->category->id,
            'organizer_id' => $this->adminUser->id,
            'title' => 'Tech Summit 2026',
            'capacity' => 100,
            'available_seats' => 100,
            'price' => 50.00,
            'start_at' => Carbon::now()->addDays(5),
            'end_at' => Carbon::now()->addDays(5)->addHours(4),
            'status' => 'PUBLISHED',
        ], $overrides));
    }

   // Attendee Access & Authentication Tests
    

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    public function test_admin_cannot_access_attendee_profile(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_my_events(): void
    {
        $response = $this->getJson('/api/myevents');

        $response->assertStatus(401);
    }

    public function test_admin_access_my_events(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/myevents');

        $response->assertStatus(403);
    }

   
    // getProfile
   

    public function test_attendee_can_fetch_profile(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'name' => $this->attendeeUser->name,
                    'email' => 'wasimchai@gmail.com',
                    'phone_number' => '1234567890',
                    'city' => 'Metropolis',
                    'state' => 'New York',
                    'zip' => '10001',
                ],
            ]);
    }

    // updateProfile 

    public function test_attendee_can_update_profile_with_valid_data(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $payload = [
            'name' => 'wasim Mohammed',
            'email' => 'wasimchaivisual@gmail.com',
            'phone_number' => '9876543210',
            'city' => 'Gotham',
            'state' => 'New Jersey',
            'zip' => '07001',
        ];

        $response = $this->patchJson('/api/profile/update', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully!',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->attendeeUser->id,
            'name' => 'wasim Mohammed',
            'email' => 'wasimchaivisual@gmail.com',
            'phone_number' => '9876543210',
            'city' => 'Gotham',
            'state' => 'New Jersey',
            'zip' => '07001',
        ]);
    }

    public function test_attendee_can_update_profile_keeping_same_email(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $payload = [
            'name' => 'Updated Name',
            'email' => $this->attendeeUser->email,
        ];

        $response = $this->patchJson('/api/profile/update', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully!',
            ]);
    }

  
  

    public function test_update_profile_fails_when_email_already_taken(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $otherUser = User::factory()->create([
            'role_id' => $this->attendeeRole->id,
            'email' => 'taken@example.com',
        ]);

        $response = $this->patchJson('/api/profile/update', [
            'name' => 'Jane Doe',
            'email' => 'taken@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonFragment(['email' => ['This email address is already in use by another account.']]);
    }

    public function test_update_profile_fails_with_invalid_phone_number(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $response = $this->patchJson('/api/profile/update', [
            'name' => 'wasim github',
            'email' => 'wasim@github.com',
            'phone_number' => '12345', // Not 10 digits
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number'])
            ->assertJsonFragment(['phone_number' => ['Please enter a valid 10-digit phone number.']]);
    }

    

    // updatePassword 
    

    public function test_attendee_can_update_password_with_valid_credentials(): void
    {
        $this->attendeeUser->update([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($this->attendeeUser);

        $payload = [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->patchJson('/api/password/update', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password update successfully!',
            ]);

        $this->attendeeUser->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->attendeeUser->password));
    }

    public function test_update_password_fails_with_wrong_current_password(): void
    {
        $this->attendeeUser->update([
            'password' => Hash::make('correctpassword'),
        ]);

        Sanctum::actingAs($this->attendeeUser);

        $payload = [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->patchJson('/api/password/update', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_update_password_fails_when_confirmation_does_not_match(): void
    {
        $this->attendeeUser->update([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($this->attendeeUser);

        $payload = [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'mismatchedpassword',
        ];

        $response = $this->patchJson('/api/password/update', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonFragment(['password' => ['The new password confirmation does not match']]);
    }

    public function test_update_password_fails_when_password_too_short(): void
    {
        $this->attendeeUser->update([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($this->attendeeUser);

        $payload = [
            'current_password' => 'oldpassword123',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        $response = $this->patchJson('/api/password/update', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

   // getmyEvents 
  
   

    public function test_attendee_sees_empty_events_when_no_registrations_exist(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $response = $this->getJson('/api/myevents');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'My Events fetched successfully!',
                'data' => [],
            ]);
    }

   
   //Admin Access & Authorization Tests
    

    public function test_unauthenticated_user_cannot_access_admin_user_routes(): void
    {
        $this->getJson('/api/admin/users')->assertStatus(401);
        $this->getJson('/api/admin/user/roles')->assertStatus(401);
        $this->getJson('/api/admin/user/1')->assertStatus(401);
        $this->postJson('/api/admin/user/update/1', [])->assertStatus(401);
        $this->postJson('/api/admin/user/delete/1')->assertStatus(401);
    }

    public function test_attendee_cannot_access_admin_user_routes(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $this->getJson('/api/admin/users')->assertStatus(403);
        $this->getJson('/api/admin/user/roles')->assertStatus(403);
        $this->getJson('/api/admin/user/1')->assertStatus(403);
        $this->postJson('/api/admin/user/update/1', [])->assertStatus(403);
        $this->postJson('/api/admin/user/delete/1')->assertStatus(403);
    }

     //getusers (GET /api/admin/users)
    
    public function test_admin_can_fetch_paginated_users(): void
    {
        Sanctum::actingAs($this->adminUser);

        User::factory()->count(4)->create([
            'role_id' => $this->attendeeRole->id,
        ]);

        $response = $this->getJson('/api/admin/users?limit=3');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Users fetched successfully!',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

   // getuser 
   

    public function test_admin_can_fetch_single_user_by_id(): void
    {
        Sanctum::actingAs($this->adminUser);

        $targetUser = User::factory()->create([
            'role_id' => $this->attendeeRole->id,
            'name' => 'Target User',
            'email' => 'target@example.com',
        ]);

        $response = $this->getJson("/api/admin/user/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User fetched successfully!',
                'data' => [
                    'id' => $targetUser->id,
                    'name' => 'Target User',
                    'email' => 'target@example.com',
                    'role' => [
                        'id' => $this->attendeeRole->id,
                        'name' => 'attendee',
                    ],
                ],
            ]);
    }

    public function test_getuser_returns_404_when_user_does_not_exist(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/user/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found.',
            ]);
    }

   

   

    // updateUser
   

    public function test_admin_can_update_user_with_valid_data(): void
    {
        Sanctum::actingAs($this->adminUser);

        $targetUser = User::factory()->create([
            'role_id' => $this->attendeeRole->id,
            'name' => 'mamdani',
            'email' => 'mamdani@gmail.com',
        ]);

        $organizerRole = Role::firstOrCreate(['name' => 'organizer']);

        $payload = [
            'name' => 'Zohran Mamdani',
            'email' => 'mayor@newyork.com',
            'phone_number' => '1122334455',
            'role_id' => $organizerRole->id,
        ];

        $response = $this->postJson("/api/admin/user/update/{$targetUser->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully!',
                'data' => [
                    'id' => $targetUser->id,
                    'name' => 'Zohran Mamdani',
                    'email' => 'mayor@newyork.com',
                    'role_id' => $organizerRole->id
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Zohran Mamdani',
            'email' => 'mayor@newyork.com',
            'role_id' => $organizerRole->id,
        ]);
    }

    public function test_update_user_returns_404_when_user_does_not_exist(): void
    {
        Sanctum::actingAs($this->adminUser);

        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role_id' => $this->attendeeRole->id,
        ];

        $response = $this->postJson('/api/admin/user/update/99999', $payload);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found.',
            ]);
    }

    public function test_update_user_fails_when_email_belongs_to_another_user(): void
    {
        Sanctum::actingAs($this->adminUser);

        $userOne = User::factory()->create([
            'role_id' => $this->attendeeRole->id,
            'email' => 'first@example.com',
        ]);

        $userTwo = User::factory()->create([
            'role_id' => $this->attendeeRole->id,
            'email' => 'second@example.com',
        ]);

        $response = $this->postJson("/api/admin/user/update/{$userTwo->id}", [
            'name' => 'User Two',
            'email' => 'first@example.com',
            'role_id' => $this->attendeeRole->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // deleteuser 
    
    public function test_admin_cannot_delete_own_account(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson("/api/admin/user/delete/{$this->adminUser->id}");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Action denied: You cannot delete your own account.',
            ]);

        $this->assertDatabaseHas('users', ['id' => $this->adminUser->id]);
    }

   
    public function test_cannot_delete_attendee_with_active_registrations(): void
    {
        Sanctum::actingAs($this->adminUser);

        $attendee = User::factory()->create([
            'role_id' => $this->attendeeRole->id,
        ]);

        $event = $this->createEvent();

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'quantity' => 1,
            'unit_price' => 50,
            'total_price' => 50,
            'registration_number' => 'REG-ACTIVE',
            'status' => 'CONFIRMED',
        ]);

        $response = $this->postJson("/api/admin/user/delete/{$attendee->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete user: This attendee has active event registrations/bookings.',
            ]);

        $this->assertDatabaseHas('users', ['id' => $attendee->id]);
    }

  
    public function test_admin_can_delete_user_without_registrations(): void
    {
        Sanctum::actingAs($this->adminUser);

        $targetUser = User::factory()->create([
            'role_id' => $this->attendeeRole->id,
        ]);

        $response = $this->postJson("/api/admin/user/delete/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }
}
