<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $attendeeUser;
    protected $adminRole;
    protected $attendeeRole;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->attendeeRole = Role::firstOrCreate(['name' => 'attendee']);

        $this->adminUser = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'status' => 'active',
        ]);

        $this->attendeeUser = User::factory()->create([
            'role_id' => $this->attendeeRole->id,
            'status' => 'active',
        ]);

        $this->category = Category::factory()->create();
    }


    protected function createEvent(array $overrides = []): Event
    {
        return Event::factory()->create(array_merge([
            'category_id' => $this->category->id,
            'capacity' => 20,
            'available_seats' => 20,
            'price' => 100,
            'start_at' => Carbon::now()->addDays(7),
            'end_at' => Carbon::now()->addDays(7)->addHours(4),
            'status' => 'PUBLISHED',
        ], $overrides));
    }

    // Booking Registration Tests 



    public function test_unauthenticated_user_cannot_register_for_event(): void
    {
        $event = $this->createEvent();

        $response = $this->postJson('/api/event/register', [
            'event_id' => $event->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(401);
    }


    public function test_non_attendee_cannot_register_for_event(): void
    {
        Sanctum::actingAs($this->adminUser);
        $event = $this->createEvent();

        $response = $this->postJson('/api/event/register', [
            'event_id' => $event->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(403);
    }


    public function test_registration_validation_fails_for_missing_fields(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $response = $this->postJson('/api/event/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['event_id', 'quantity']);
    }


    public function test_registration_fails_when_event_does_not_exist(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $response = $this->postJson('/api/event/register', [
            'event_id' => 99999,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['event_id']);
    }


    public function test_registration_fails_when_quantity_is_less_than_one(): void
    {
        Sanctum::actingAs($this->attendeeUser);
        $event = $this->createEvent();

        $response = $this->postJson('/api/event/register', [
            'event_id' => $event->id,
            'quantity' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }


    public function test_attendee_can_successfully_register_for_event(): void
    {
        Sanctum::actingAs($this->attendeeUser);
        $event = $this->createEvent([
            'available_seats' => 15,
            'price' => 50,
        ]);

        $response = $this->postJson('/api/event/register', [
            'event_id' => $event->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Registration successful',
                'registration' => [
                    'event_id' => $event->id,
                    'user_id' => $this->attendeeUser->id,
                    'quantity' => 2,
                    'unit_price' => 50,
                    'total_price' => 100,
                ],
            ]);

        // Verify registration number format
        $registrationData = $response->json('registration');
        $this->assertMatchesRegularExpression('/^REG-\d{6}$/', $registrationData['registration_number']);

        // Verify event available_seats was decremented
        $this->assertEquals(13, $event->fresh()->available_seats);

        // Verify database record exists
        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $this->attendeeUser->id,
            'quantity' => 2,
            'unit_price' => 50,
            'total_price' => 100,
        ]);
    }


    public function test_registration_fails_when_event_is_sold_out(): void
    {
        Sanctum::actingAs($this->attendeeUser);
        $event = $this->createEvent(['available_seats' => 0]);

        $response = $this->postJson('/api/event/register', [
            'event_id' => $event->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Seats are completely filled for this event.',
            ]);
    }


    public function test_registration_fails_when_quantity_exceeds_available_seats(): void
    {
        Sanctum::actingAs($this->attendeeUser);
        $event = $this->createEvent(['available_seats' => 2]);

        $response = $this->postJson('/api/event/register', [
            'event_id' => $event->id,
            'quantity' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Only 2 seat(s) remaining for this event.',
            ]);
    }


    public function test_attendee_cannot_register_twice_for_the_same_event(): void
    {
        Sanctum::actingAs($this->attendeeUser);
        $event = $this->createEvent(['available_seats' => 10]);

        // First registration
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->attendeeUser->id,
            'quantity' => 1,
            'unit_price' => 100,
            'total_price' => 100,
            'registration_number' => 'REG-000001',
            'status' => 'CONFIRMED',
        ]);

        // Second registration attempt
        $response = $this->postJson('/api/event/register', [
            'event_id' => $event->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'You have already registered for this event.',
            ]);
    }



    // Admin Bookings List Tests


    public function test_unauthenticated_user_cannot_access_bookings_list(): void
    {
        $response = $this->getJson('/api/admin/bookings');

        $response->assertStatus(401);
    }


    public function test_attendee_cannot_access_admin_bookings_list(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $response = $this->getJson('/api/admin/bookings');

        $response->assertStatus(403);
    }


    public function test_admin_can_retrieve_paginated_bookings_list(): void
    {
        Sanctum::actingAs($this->adminUser);
        $event = $this->createEvent();

        $attendee2 = User::factory()->create(['role_id' => $this->attendeeRole->id]);
        $attendee3 = User::factory()->create(['role_id' => $this->attendeeRole->id]);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->attendeeUser->id,
            'quantity' => 1,
            'unit_price' => 100,
            'total_price' => 100,
            'registration_number' => 'REG-000001',
            'status' => 'CONFIRMED',
        ]);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $attendee2->id,
            'quantity' => 2,
            'unit_price' => 100,
            'total_price' => 200,
            'registration_number' => 'REG-000002',
            'status' => 'CONFIRMED',
        ]);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $attendee3->id,
            'quantity' => 1,
            'unit_price' => 100,
            'total_price' => 100,
            'registration_number' => 'REG-000003',
            'status' => 'CONFIRMED',
        ]);

        $response = $this->getJson('/api/admin/bookings?limit=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'event_id',
                        'user_id',
                        'registration_number',
                        'quantity',
                        'total_price',
                        'event' => ['id', 'title'],
                        'user' => ['id', 'name'],
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(3, $response->json('pagination.total'));
    }

    // Admin Single Booking Tests (GET /api/admin/bookings/get/{id})


    public function test_unauthenticated_user_cannot_view_booking_details(): void
    {
        $response = $this->getJson('/api/admin/bookings/get/1');

        $response->assertStatus(401);
    }


    public function test_attendee_cannot_view_admin_booking_details(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $response = $this->getJson('/api/admin/bookings/get/1');

        $response->assertStatus(403);
    }


    public function test_admin_receives_404_when_booking_not_found(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/bookings/get/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Booking not found.',
            ]);
    }


    public function test_admin_can_view_booking_details_successfully(): void
    {
        Sanctum::actingAs($this->adminUser);
        $event = $this->createEvent(['title' => 'Tech Summit 2026']);

        $booking = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->attendeeUser->id,
            'quantity' => 2,
            'unit_price' => 150,
            'total_price' => 300,
            'registration_number' => 'REG-123456',
            'status' => 'CONFIRMED',
        ]);

        $response = $this->getJson("/api/admin/bookings/get/{$booking->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Booking details retrieved successfully.',
                'data' => [
                    'id' => $booking->id,
                    'registration_number' => 'REG-123456',
                    'event' => [
                        'id' => $event->id,
                        'title' => 'Tech Summit 2026',
                    ],
                    'user' => [
                        'id' => $this->attendeeUser->id,
                        'name' => $this->attendeeUser->name,
                    ],
                ],
            ]);
    }


    // Admin Delete Booking Tests(/admin/booking/delete/{id})


    public function test_unauthenticated_user_cannot_delete_booking(): void
    {
        $response = $this->postJson('/api/admin/booking/delete/1');

        $response->assertStatus(401);
    }


    public function test_attendee_cannot_delete_booking(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $response = $this->postJson('/api/admin/booking/delete/1');

        $response->assertStatus(403);
    }


    public function test_admin_receives_404_when_deleting_non_existent_booking(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/booking/delete/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Booking not found.',
            ]);
    }


    public function test_admin_cannot_delete_booking_for_past_event(): void
    {
        Sanctum::actingAs($this->adminUser);
        $event = $this->createEvent([
            'start_at' => Carbon::now()->subDays(2),
            'end_at' => Carbon::now()->subDays(2)->addHours(4),
        ]);

        $booking = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->attendeeUser->id,
            'quantity' => 1,
            'unit_price' => 50,
            'total_price' => 50,
            'registration_number' => 'REG-111222',
            'status' => 'CONFIRMED',
        ]);

        $response = $this->postJson("/api/admin/booking/delete/{$booking->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete a booking for an event that has already started.',
            ]);

        // Assert booking was not deleted
        $this->assertDatabaseHas('event_registrations', [
            'id' => $booking->id,
        ]);
    }


    public function test_admin_can_delete_booking_and_seats_are_restored(): void
    {
        Sanctum::actingAs($this->adminUser);
        $event = $this->createEvent([
            'available_seats' => 8,
            'start_at' => Carbon::now()->addDays(5),
            'end_at' => Carbon::now()->addDays(5)->addHours(3),
        ]);

        $booking = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->attendeeUser->id,
            'quantity' => 3,
            'unit_price' => 100,
            'total_price' => 300,
            'registration_number' => 'REG-333444',
            'status' => 'CONFIRMED',
        ]);

        $response = $this->postJson("/api/admin/booking/delete/{$booking->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Booking deleted successfully and seats restored.',
                'data' => [
                    'restored_seats' => 3,
                    'available_seats' => 11,
                ],
            ]);

        // Assert seats were restored on the event
        $this->assertEquals(11, $event->fresh()->available_seats);

        // Assert booking was deleted from database
        $this->assertDatabaseMissing('event_registrations', [
            'id' => $booking->id,
        ]);
    }
}
