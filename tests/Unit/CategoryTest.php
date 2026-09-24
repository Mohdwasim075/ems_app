<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;
   
    private User $adminUser;
    private User $regularUser;

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


    // category route accessble by users
    

    public function test_unauthenticated_user_cannot_access_categories(){

        $response = $this->getJson('api/admin/categories');

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_categories(){
       $attendeeRole = Role::where('name', 'attendee')->firstOrFail();

    $attendee = User::factory()->create([
        'role_id' => $attendeeRole->id,
    ]);

    $response = $this
        ->actingAs($attendee)
        ->get('/admin/categories');

    $response->assertStatus(403);
    }

    

    

    //getCategories 


    public function test_admin_can_get_paginated_categories_with_default_limit(): void
    {
        Sanctum::actingAs($this->adminUser);

        // Create 15 categories
        Category::factory()->count(15)->create();

        $response = $this->getJson('/api/admin/categories');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Event categories fetched successfully!',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'description', 'is_active', 'created_at', 'updated_at'],
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

    //     // Default limit is 10
    //     $this->assertCount(10, $response->json('data'));
    //     $this->assertEquals(15, $response->json('pagination.total'));
    //     $this->assertEquals(10, $response->json('pagination.per_page'));
     }

    public function test_admin_can_get_paginated_categories_with_custom_limit(): void
    {
        Sanctum::actingAs($this->adminUser);

        Category::factory()->count(8)->create();

        $response = $this->getJson('/api/admin/categories?limit=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $this->assertEquals(3, $response->json('pagination.per_page'));
        $this->assertEquals(8, $response->json('pagination.total'));
        $this->assertEquals(3, $response->json('pagination.last_page'));
    }

  

    //  categoryList 




    public function test_category_list_returns_empty_array_when_none_exist(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/categories/list');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'category list fetched successfully',
                'data' => [],
            ]);
    }

     // show (Fetch Single Category)
   

    public function test_admin_can_fetch_single_category_by_id(): void
    {
        
         $adminRole = Role::where('name', 'admin')->firstOrFail();
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $category = Category::factory()->create([
            'name' => 'Technology',
            'description' => 'Tech events and conferences',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->getJson("/api/admin/category/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'category  fetched',
                'category' => [
                    [
                        'id' => $category->id,
                        'name' => 'Technology',
                        'description' => 'Tech events and conferences',
                        'is_active' => 1,
                    ],
                ],
            ]);
    }

    public function test_show_returns_empty_array_for_non_existent_category_id(): void
    {
         $adminRole = Role::where('name', 'admin')->firstOrFail();
         $admin = User::factory()->create([
                'role_id' => $adminRole->id,
            ]);

        $response = $this->actingAs($admin)->getJson('/api/admin/category/99999');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'category  fetched',
                'category' => [],
            ]);
    }

  
    //  createCategory
 

    public function test_admin_can_create_category_with_valid_data(): void
    {
        Sanctum::actingAs($this->adminUser);

        $payload = [
            'name' => 'Design & Art',
            'description' => 'Design conferences and workshops',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/admin/category/create', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Category created successfully!',
                'category' => [
                    'name' => 'Design & Art',
                    'description' => 'Design conferences and workshops',
                    'is_active' => 1,
                ],
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Design & Art',
            'description' => 'Design conferences and workshops',
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_create_category_with_null_description(): void
    {
        Sanctum::actingAs($this->adminUser);

        $payload = [
            'name' => 'Music Festivals',
            'description' => null,
            'is_active' => false,
        ];

        $response = $this->postJson('/api/admin/category/create', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Category created successfully!',
                'category' => [
                    'name' => 'Music Festivals',
                    'description' => null,
                    'is_active' => 0,
                ],
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Music Festivals',
            'description' => null,
            'is_active' => 0,
        ]);
    }

    public function test_create_category_fails_when_required_fields_missing(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/category/create', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_active']);
    }

   

   

  

    public function test_admin_can_update_category_with_valid_data(): void
    {
        Sanctum::actingAs($this->adminUser);

        $category = Category::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Updated Name',
            'description' => 'Updated Short Description',
            'is_active' => false,
        ];

        $response = $this->postJson("/api/admin/category/update/{$category->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'category updated successfully!',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'description' => 'Updated Short Description',
            'is_active' => 0,
        ]);
    }

    public function test_update_category_returns_404_if_category_does_not_exist(): void
    {
        Sanctum::actingAs($this->adminUser);

        $payload = [
            'name' => 'Non Existent',
            'description' => 'Some description',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/admin/category/update/99999', $payload);

        $response->assertStatus(404);
    }

    public function test_update_category_fails_when_required_fields_missing(): void
    {
        Sanctum::actingAs($this->adminUser);

        $category = Category::factory()->create();

        $response = $this->postJson("/api/admin/category/update/{$category->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description', 'is_active'])
            ->assertJsonFragment(['name' => ['Category name is required']]);
    }

    

    public function test_update_category_fails_when_is_active_is_not_boolean(): void
    {
        Sanctum::actingAs($this->adminUser);

        $category = Category::factory()->create();

        $payload = [
            'name' => 'Valid Name',
            'description' => 'Valid Description',
            'is_active' => 'not-a-bool',
        ];

        $response = $this->postJson("/api/admin/category/update/{$category->id}", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment(['is_active' => ['Invalid status value provided']]);
    }

     // deleteCategory (Delete Category)
 

    public function test_admin_can_delete_category_with_no_associated_events(): void
    {
        Sanctum::actingAs($this->adminUser);

        $category = Category::factory()->create();

        $response = $this->postJson("/api/admin/category/delete/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'category deleted successfully!',
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_cannot_delete_category_with_associated_events(): void
    {
        Sanctum::actingAs($this->adminUser);

        $category = Category::factory()->create();

        // Create an event linked to this category
        Event::factory()->create([
            'category_id' => $category->id,
            'organizer_id' => $this->adminUser->id,
        ]);

        $response = $this->postJson("/api/admin/category/delete/{$category->id}");

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'cannot delete category which has events',
            ]);

        // Verify category is not deleted
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_delete_category_returns_404_if_category_does_not_exist(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/category/delete/99999');

        $response->assertStatus(404);
    }
}
