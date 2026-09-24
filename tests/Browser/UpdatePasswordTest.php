<?php

namespace Tests\Browser;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UpdatePasswordTest extends DuskTestCase
{
    use DatabaseTruncation;


    protected function createAttendee(string $password = 'oldpassword123')
    {
        $role = Role::firstOrCreate(['name' => 'attendee']);

        return User::factory()->create([
            'role_id' => $role->id,
            'password' => Hash::make($password),
        ]);
    }


    public function test_user_cannot_update_password_with_empty_fields()
    {
        $user = $this->createAttendee();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitFor('#passwordResetForm')
                ->press('#passwordResetForm button[type="submit"]')
                ->waitFor('.error-current_password')
                ->assertSeeIn('.error-current_password', 'The current password field is required.')
                ->assertSeeIn('.error-password', 'Please enter a new password');
        });
    }


    public function test_user_cannot_update_password_with_incorrect_current_password()
    {
        $user = $this->createAttendee('789456');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitFor('#passwordResetForm')
                ->type('#current_password', '456123')
                ->type('#password', '741852')
                ->type('#password_confirmation', '741852')
                ->press('#passwordResetForm button[type="submit"]')
                ->waitForText('Your current password is incorrect.')
                ->assertSee('Your current password is incorrect.');
        });
    }


    public function test_user_cannot_update_password_when_confirmation_does_not_match()
    {
        $user = $this->createAttendee('789456');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitFor('#passwordResetForm')
                ->type('#current_password', '789456')
                ->type('#password', '456123')
                ->type('#password_confirmation', '456789')
                ->press('#passwordResetForm button[type="submit"]')
                ->waitFor('.error-password')
                ->assertSeeIn('.error-password', 'The new password confirmation does not match');
        });
    }


    public function test_user_cannot_update_password_with_short_password()
    {
        $user = $this->createAttendee('789456');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitFor('#passwordResetForm')
                ->type('#current_password', '789456')
                ->type('#password', '456123')
                ->type('#password_confirmation', '456123')
                ->press('#passwordResetForm button[type="submit"]')
                ->waitForText('The password field must be at least 6 characters.')
                ->assertSee('The password field must be at least 6 characters.');
        });
    }


    public function test_user_can_successfully_update_password()
    {
        $oldPassword = '456123';
        $newPassword = '789456';
        $user = $this->createAttendee($oldPassword);

        $this->browse(function (Browser $browser) use ($user, $oldPassword, $newPassword) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitFor('#passwordResetForm')
                ->type('#current_password', $oldPassword)
                ->type('#password', $newPassword)
                ->type('#password_confirmation', $newPassword)
                ->press('#passwordResetForm button[type="submit"]')
                ->waitForDialog()
                ->assertDialogOpened('Password updated successfully!')
                ->acceptDialog();
        });

        // Verify password was updated in database
        $this->assertTrue(
            Hash::check($newPassword, $user->fresh()->password),

        );
        $this->assertFalse(
            Hash::check($oldPassword, $user->fresh()->password),

        );
    }
}
