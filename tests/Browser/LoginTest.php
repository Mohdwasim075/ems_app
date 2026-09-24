<?php

namespace Tests\Browser;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseTruncation;

  
    public function test_login_page_renders_successfully(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('Log In')
                ->assertPresent('#email-input')
                ->pause(2000)
                ->assertPresent('#password-input')
                ->pause(2000)
                ->assertPresent('#login-btn')
                ->pause(2000)
                ->assertSeeLink('Forgot password?')
                ->assertSeeLink('Register');
        });
    }

    
    public function test_user_cannot_login_with_empty_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(2000)
                ->press('#login-btn')
                ->waitForText('The email field is required')
                ->pause(2000)
                ->assertSee('The email field is required')
                ->assertSee('The password field should not be empty');
        });
    }

   
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(2000)
                ->type('#email-input', 'nonexistent@example.com')
                ->pause(2000)
                ->type('#password-input', 'invalidpassword')
                ->pause(2000)
                ->press('#login-btn')
                ->pause(2000)
                ->waitFor('#alert-container .alert-danger')
                ->pause(2000)
                ->assertSeeIn('#alert-container', 'Invalid email or password');
        });
    }

   
    public function test_admin_can_login_and_is_redirected_to_admin_dashboard(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/login')
                ->pause(2000)
                ->type('#email-input', 'admin@example.com')
                ->pause(2000)
                ->type('#password-input', 'password123')
                ->pause(2000)
                ->press('#login-btn')
                ->waitForLocation('/admin/dashboard')
                ->pause(2000)
                ->assertPathIs('/admin/dashboard')
                ->pause(2000)
                ->assertAuthenticatedAs($admin)
                ->pause(2000)
                ->press('.logout-btn');
        });
    }

    
    public function test_attendee_can_login_and_is_redirected_to_home_page(): void
    {
        $attendeeRole = Role::firstOrCreate(['name' => 'attendee']);
        $attendee = User::factory()->create([
            'role_id' => $attendeeRole->id,
            'email' => 'attendee@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->browse(function (Browser $browser) use ($attendee) {
            $browser->visit('/login')
                ->type('#email-input', 'attendee@example.com')
                ->pause(2000)
                ->type('#password-input', 'password123')
                ->pause(2000)
                ->press('#login-btn')
                ->pause(2000)
                ->waitForLocation('/')
                ->pause(2000)
                ->assertPathIs('/')
                ->pause(2000)
                ->assertAuthenticatedAs($attendee)
                ->pause(2000);
        });
    }

    //     public function test_login_page(){
//         $this->browse(function (Browser $browser) {
//         $browser->visit('/login')
//             ->screenshot('login-page')
//             ->assertPathIs('/login');

    //       });
// }
}