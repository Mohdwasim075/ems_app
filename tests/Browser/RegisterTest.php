<?php

namespace Tests\Browser;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RegisterTest extends DuskTestCase
{
    use DatabaseMigrations;



    public function test_user_cannot_register_with_empty_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->pause(2000)
                ->press('#create-btn')
                ->waitForText('The name field is required.')
                ->pause(2000)
                ->assertSee('The name field is required.')
                ->assertSee('The email field is required.')
                ->assertSee('The password field is required.');
        });
    }

    
    public function test_user_cannot_register_when_passwords_do_not_match(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->pause(2000)
                ->type('#fullname-input', 'wasim Mohammed')
                ->pause(2000)
                ->type('#email-input', 'wasimchai@gmail.com')
                ->pause(2000)
                ->type('#password-input', '789456')
                ->pause(2000)
                ->type('#retypePassword-input', '456123')
                ->pause(2000)
                ->press('#create-btn')
                ->waitForText('The password field confirmation does not match.')
                ->pause(2000)
                ->assertSee('The password field confirmation does not match.');
        });
    }


    public function test_user_cannot_register_with_existing_email(): void
    {
        $role = Role::firstOrCreate(['name' => 'attendee']);
        User::factory()->create([
            'role_id' => $role->id,
            'email' => 'wasimchai@gmail.com',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->pause(2000)
                ->type('#fullname-input', 'wasim Mohammed')
                ->pause(2000)
                ->type('#email-input', 'wasimchai@gmail.com')
                ->pause(2000)
                ->type('#password-input', '789456')
                ->pause(2000)
                ->type('#retypePassword-input', '789456')
                ->pause(2000)
                ->press('#create-btn')
                ->waitForText('The email has already been taken.')
                ->pause(2000)
                ->assertSee('The email has already been taken.');
        });
    }

   
    public function test_user_can_register_successfully_and_redirects_to_login(): void
    {
        Role::firstOrCreate(['name' => 'attendee']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->pause(2000)
                ->type('#fullname-input', 'wasim Mohammed')
                ->pause(2000)
                ->type('#email-input', 'wasimchai@gmail.com')
                ->pause(2000)
                ->type('#password-input', '789456')
                ->pause(2000)
                ->type('#retypePassword-input', '789456')
                ->pause(2000)
                ->press('#create-btn')
                ->waitFor('#resetAlert.alert-success', 7)
                ->assertSeeIn('#resetAlert', 'User registered successfully!')
                ->waitForLocation('/login', 7)
                ->pause(2000)
                ->assertPathIs('/login');
        });

        $this->assertDatabaseHas('users', [
            'name' => 'wasim Mohammed',
            'email' => 'wasimchai@gmail.com',
        ]);
    }
}
