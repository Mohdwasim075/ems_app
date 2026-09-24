<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $attendeeUser;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // roles and users
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $attendeeRole = Role::firstOrCreate(['name' => 'attendee']);

        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);
        $this->attendeeUser = User::factory()->create(['role_id' => $attendeeRole->id]);


        $this->category = Category::factory()->create();
    }



    private function validEventPayload(array $overrides = [])
    {
        return array_merge([
            'title' => 'Annual Tech Summit',
            'category_id' => $this->category->id,
            'price' => 100,
            'status' => 'published',
            'location' => 'Hyderabad',
            'start_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(10)->addHours(4)->format('Y-m-d H:i:s'),
            'capacity' => 200,
            'description' => 'A two-day tech summit covering AI and cloud.',
        ], $overrides);
    }

    private function validUpdatePayload(array $overrides = [])
    {
        return array_merge([
            'title' => 'Updated Tech Summit',
            'category_id' => $this->category->id,
            'description' => 'Updated description.',
            'start_at' => now()->addDays(15)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(15)->addHours(4)->format('Y-m-d H:i:s'),
            'location' => 'Mumbai',
            'price' => 200,
            'capacity' => 200,
            'status' => 'published',
        ], $overrides);
    }




    public function test_public_index_returns_only_published_events()
    {
        Event::factory()->count(3)->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'status' => 'published',
        ]);

        Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data',
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ],
            ]);

        // Only 3 published events should be returned
        $this->assertCount(3, $response->json('data'));
    }

    public function test_public_index_respects_custom_limit(): void
    {
        Event::factory()->count(10)->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/events?limit=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $this->assertEquals(3, $response->json('pagination.per_page'));
        $this->assertEquals(10, $response->json('pagination.total'));
    }

    public function test_public_index_returns_empty_data_when_no_published_events()
    {
        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
        $this->assertEquals(0, $response->json('pagination.total'));
    }


    //  events/show

    public function test_public_show_returns_event_details()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'title' => 'Laravel Conference',
        ]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'event_date',
                    'end_time',
                    'location',
                    'available_seats',
                    'price',

                ],
            ]);

        $this->assertEquals('Laravel Conference', $response->json('data.title'));
    }

    public function test_public_show_returns_404_for_nonexistent_event()
    {
        $response = $this->getJson('/api/events/99999');

        $response->assertStatus(404);
    }


    // events/featured


    public function test_public_featured_returns_top_3_events()
    {
        Event::factory()->count(5)->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/events/featured');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Top 3 registered events fetched successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);

        // Should return at most 3
        $this->assertLessThanOrEqual(3, count($response->json('data')));
    }

    public function test_public_featured_returns_empty_when_no_published_events()
    {
        $response = $this->getJson('/api/events/featured');

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'data' => []]);
    }


    //  events/upcoming


    public function test_public_upcoming_returns_future_published_events()
    {
        // future published event
        Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'status' => 'published',
            'start_at' => now()->addDays(5),
        ]);

        // past event — should NOT appear
        Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'status' => 'published',
            'start_at' => now()->subDays(5),
        ]);

        $response = $this->getJson('/api/events/upcoming');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'data']);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_public_upcoming_does_not_return_draft_events(): void
    {
        Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'status' => 'draft',
            'start_at' => now()->addDays(3),
        ]);

        $response = $this->getJson('/api/events/upcoming');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }


    //getEvents()  

    public function test_unauthenticated_user_cannot_access_admin_events()
    {
        $response = $this->getJson('/api/admin/events');

        $response->assertStatus(401);
    }

    public function test_attendee_cannot_access_admin_events()
    {
        Sanctum::actingAs($this->attendeeUser);

        $response = $this->getJson('/api/admin/events');

        $response->assertStatus(403);
    }

    public function test_admin_get_events_returns_message_when_no_events_exist()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/events');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'You have no events, please create events',
            ]);
    }

    public function test_admin_get_events_returns_paginated_list()
    {
        Sanctum::actingAs($this->adminUser);

        Event::factory()->count(15)->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/admin/events');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Events fetched successfully!'])
            ->assertJsonStructure([
                'message',
                'data',
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ],
            ]);

        // Default limit is 10
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(15, $response->json('pagination.total'));
    }

    public function test_admin_get_events_respects_custom_limit()
    {
        Sanctum::actingAs($this->adminUser);

        Event::factory()->count(5)->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/admin/events?limit=2');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(5, $response->json('pagination.total'));
    }


    //  getEvent() api/admin/events/{id}


    public function test_admin_can_fetch_single_event_by_id()
    {
        Sanctum::actingAs($this->adminUser);

        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'title' => 'Spring Gala',
        ]);

        $response = $this->getJson("/api/admin/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'specific event fetched successfully!',
            ])
            ->assertJsonPath('data.id', $event->id)
            ->assertJsonPath('data.title', 'Spring Gala');
    }

    public function test_admin_get_single_event_returns_404_for_nonexistent_event()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/events/99999');

        $response->assertStatus(404);
    }


    //  createEvent()  /admin/events/create

    public function test_admin_can_create_event_with_valid_data()
    {
        Sanctum::actingAs($this->adminUser);

        $payload = $this->validEventPayload();

        $response = $this->postJson('/api/admin/events/create', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Event created successfully!',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'title', 'category'],
            ]);

        $this->assertDatabaseHas('events', [
            'title' => 'Annual Tech Summit',
            'location' => 'Hyderabad',
            'status' => 'published',
            'capacity' => 200,
        ]);
    }

    public function test_create_event_sets_available_seats_to_capacity()
    {
        Sanctum::actingAs($this->adminUser);

        $payload = $this->validEventPayload(['capacity' => 150]);

        $this->postJson('/api/admin/events/create', $payload)->assertStatus(201);

        $this->assertDatabaseHas('events', [
            'capacity' => 150,
            'available_seats' => 150,
        ]);
    }

    public function test_create_event_sets_organizer_to_authenticated_user()
    {
        Sanctum::actingAs($this->adminUser);

        $this->postJson('/api/admin/events/create', $this->validEventPayload())->assertStatus(201);

        $this->assertDatabaseHas('events', ['organizer_id' => $this->adminUser->id]);
    }

    public function test_create_event_fails_when_required_fields_are_missing()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/events/create', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'category_id',
                'price',
                'status',
                'location',
                'start_at',
                'end_at',
                'capacity',
            ]);
    }

    public function test_create_event_fails_when_title_exceeds_max_length(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/events/create', $this->validEventPayload([
            'title' => str_repeat('A', 151),
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_create_event_fails_when_category_id_does_not_exist(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/events/create', $this->validEventPayload([
            'category_id' => 99999,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_create_event_fails_with_invalid_status(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/events/create', $this->validEventPayload([
            'status' => 'cancelled',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_create_event_fails_when_end_at_is_before_start_at(): void
    {
        Sanctum::actingAs($this->adminUser);

        $start = now()->addDays(10)->format('Y-m-d H:i:s');
        $end = now()->addDays(9)->format('Y-m-d H:i:s');

        $response = $this->postJson('/api/admin/events/create', $this->validEventPayload([
            'start_at' => $start,
            'end_at' => $end,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_at']);
    }

    public function test_create_event_fails_with_negative_price(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/events/create', $this->validEventPayload([
            'price' => -10,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_create_event_fails_with_zero_capacity(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/events/create', $this->validEventPayload([
            'capacity' => 0,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['capacity']);
    }



    public function test_create_event_allows_null_description(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/events/create', $this->validEventPayload([
            'description' => null,
        ]));

        $response->assertStatus(201);
        $this->assertDatabaseHas('events', ['description' => null]);
    }

    public function test_create_event_fails_when_description_exceeds_max_length(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/events/create', $this->validEventPayload([
            'description' => str_repeat('D', 1001),
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function test_unauthenticated_user_cannot_create_event(): void
    {
        $response = $this->postJson('/api/admin/events/create', $this->validEventPayload());

        $response->assertStatus(401);
    }

    public function test_attendee_cannot_create_event(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $response = $this->postJson('/api/admin/events/create', $this->validEventPayload());

        $response->assertStatus(403);
    }

    // updateEvent()  /admin/events/update/{id}


    public function test_admin_can_update_event_with_valid_data(): void
    {
        Sanctum::actingAs($this->adminUser);

        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $payload = $this->validUpdatePayload();

        $response = $this->patchJson("/api/admin/events/update/{$event->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Event updated successfully!',
            ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Tech Summit',
            'location' => 'Mumbai',
        ]);
    }

    public function test_update_event_returns_404_for_nonexistent_event(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->patchJson('/api/admin/events/update/99999', $this->validUpdatePayload());

        $response->assertStatus(404);
    }

    public function test_update_event_fails_when_required_fields_are_missing(): void
    {
        Sanctum::actingAs($this->adminUser);

        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->patchJson("/api/admin/events/update/{$event->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'start_at', 'end_at', 'price', 'status']);
    }

    public function test_update_event_fails_when_title_exceeds_max_length(): void
    {
        Sanctum::actingAs($this->adminUser);

        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->patchJson("/api/admin/events/update/{$event->id}", $this->validUpdatePayload([
            'title' => str_repeat('T', 151),
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_update_event_fails_when_end_at_is_before_start_at(): void
    {
        Sanctum::actingAs($this->adminUser);

        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $start = now()->addDays(10)->format('Y-m-d H:i:s');
        $end = now()->addDays(9)->format('Y-m-d H:i:s');

        $response = $this->patchJson("/api/admin/events/update/{$event->id}", $this->validUpdatePayload([
            'start_at' => $start,
            'end_at' => $end,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_at']);
    }

    public function test_update_event_fails_with_negative_price(): void
    {
        Sanctum::actingAs($this->adminUser);

        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->patchJson("/api/admin/events/update/{$event->id}", $this->validUpdatePayload([
            'price' => -5,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_unauthenticated_user_cannot_update_event(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->patchJson("/api/admin/events/update/{$event->id}", $this->validUpdatePayload());

        $response->assertStatus(401);
    }

    public function test_attendee_cannot_update_event(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->patchJson("/api/admin/events/update/{$event->id}", $this->validUpdatePayload());

        $response->assertStatus(403);
    }


    //deleteEvent() /admin/events/delete/{id}


    public function test_admin_can_delete_event_with_no_registrations(): void
    {
        Sanctum::actingAs($this->adminUser);

        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->postJson("/api/admin/events/delete/{$event->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'event deleted successfully!.']);

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_cannot_delete_event_that_has_registrations(): void
    {
        Sanctum::actingAs($this->adminUser);

        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        // Attach a registration to the event
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $this->attendeeUser->id,
            'unit_price' => '100'
        ]);

        $response = $this->postJson("/api/admin/events/delete/{$event->id}");

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'cannot delete event which has event registrations!.',
            ]);

        // Event should still be in DB
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_delete_event_returns_404_for_nonexistent_event(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/events/delete/99999');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_delete_event(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->postJson("/api/admin/events/delete/{$event->id}");

        $response->assertStatus(401);
    }

    public function test_attendee_cannot_delete_event(): void
    {
        Sanctum::actingAs($this->attendeeUser);

        $event = Event::factory()->create([
            'organizer_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->postJson("/api/admin/events/delete/{$event->id}");

        $response->assertStatus(403);
    }
}
