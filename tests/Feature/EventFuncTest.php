<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventFuncTest extends TestCase
{
    use RefreshDatabase;

     protected function setUp(): void
    {
        parent::setUp();

        // Create Admin Role and Admin User
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        // Create Attendee/Regular Role and Regular User
        $regularRole = Role::firstOrCreate(['name' => 'attendee']);
        $this->regularUser = User::factory()->create([
            'role_id' => $regularRole->id,
        ]);
    }

    public function test__events_public_route_returns_success(){
        
        $response = $this->getJson('/events');

        $response->assertStatus(200);
    }

    public function test_events_api_returns_json(): void
{
    $response = $this->getJson('/api/events');

    $response->assertStatus(200);

    $response->assertJsonStructure([
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
}

        public function test_unathenticatedUser_cannot_access_admin_categories(){

            Sanctum::actingAs($this->regularUser);

            $response = $this->get('/admin/categories');

            $response->assertStatus(403);


        }

        // public function test_admin_can_



    // /** @test */
    // public function test_fetches_a_specific_event_successfully(): void
    // {
    //     // 1. Arrange: Create an event in the test database
    //     $event = Event::factory()->create([
    //         'title' => 'Laravel Tech Conference',
    //         'location' => 'Main Auditorium',
    //     ]);

    //     // 2. Act: Make an HTTP GET request to the event route
    //     // Replace 'events.show' with your actual route name, or use url("/api/events/{$event->id}")
    //     $response = $this->getJson(route('events.show', $event->id));

    //     // 3. Assert: Verify response status and JSON structure
    //     $response->assertStatus(200)
    //              ->assertJson([
    //                  'message' => 'specific event fetched successfully!',
    //                  'data' => [
    //                      'id' => $event->id,
    //                      'title' => 'Laravel Tech Conference',
    //                      'location' => 'Main Auditorium',
    //                  ],
    //              ]);
    // }

    // /** @test */
    // public function test_returns_404_when_event_is_not_found(): void
    // {
    //     // Act: Request a non-existent event ID
    //     $nonExistentId = 9999;
    //     $response = $this->getJson(route('events.show', $nonExistentId));

    //     // Assert: Verify HTTP 404 status
    //     $response->assertStatus(404);
    // }
}
