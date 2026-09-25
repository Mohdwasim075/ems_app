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

    protected function createAttendee(string $password = 'oldpassword123'): User
    {
        $role = Role::firstOrCreate(['name' => 'attendee']);

        return User::factory()->create([
            'role_id' => $role->id,
            'password' => Hash::make($password),
        ]);
    }

    public function test_user_cannot_update_password_with_empty_fields(): void
    {
        $user = $this->createAttendee();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitFor('#passwordResetForm')
                ->within('#passwordResetForm', function (Browser $form) {
                    $form->press('button[type="submit"]');
                })
                ->waitFor('.error-current_password')
                ->assertSeeIn('.error-current_password', 'The current password field is required.')
                ->assertSeeIn('.error-password', 'Please enter a new password')
                ->logout();
        });
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = $this->createAttendee('789456');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitFor('#passwordResetForm')
                ->within('#passwordResetForm', function (Browser $form) {
                    $form->clear('#current_password')->type('#current_password', '456123')
                        ->clear('#password')->type('#password', '741852')
                        ->clear('#password_confirmation')->type('#password_confirmation', '741852')
                        ->press('button[type="submit"]');
                })
                ->waitForText('Your current password is incorrect.')
                ->assertSee('Your current password is incorrect.')
                ->logout();
        });
    }

    public function test_user_cannot_update_password_when_confirmation_does_not_match(): void
    {
        $user = $this->createAttendee('789456');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitFor('#passwordResetForm')
                ->within('#passwordResetForm', function (Browser $form) {
                    $form->clear('#current_password')->type('#current_password', '789456')
                        ->clear('#password')->type('#password', '456123')
                        ->clear('#password_confirmation')->type('#password_confirmation', '456789')
                        ->press('button[type="submit"]');
                })
                ->waitFor('.error-password')
                ->assertSeeIn('.error-password', 'The new password confirmation does not match')
                ->logout();
        });
    }

    public function test_user_cannot_update_password_with_short_password(): void
    {
        $user = $this->createAttendee('789456');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitFor('#passwordResetForm')
                ->within('#passwordResetForm', function (Browser $form) {
                    $form->clear('#current_password')->type('#current_password', '789456')
                        ->clear('#password')->type('#password', '4561')
                        ->clear('#password_confirmation')->type('#password_confirmation', '4561')
                        ->press('button[type="submit"]');
                })
                ->waitForText('The password field must be at least 6 characters.')
                ->assertSee('The password field must be at least 6 characters.')
                ->logout();
        });
    }

    public function test_user_can_successfully_update_password(): void
    {
        $oldPassword = '456123';
        $newPassword = '789456';
        $user = $this->createAttendee($oldPassword);

        $this->browse(function (Browser $browser) use ($user, $oldPassword, $newPassword) {
            $browser->loginAs($user)
                ->visit('/profile')
                ->waitFor('#passwordResetForm')
                ->within('#passwordResetForm', function (Browser $form) use ($oldPassword, $newPassword) {
                    $form->clear('#current_password')->type('#current_password', $oldPassword)
                        ->clear('#password')->type('#password', $newPassword)
                        ->clear('#password_confirmation')->type('#password_confirmation', $newPassword)
                        ->press('button[type="submit"]');
                })
                ->waitForDialog()
                ->assertDialogOpened('Password updated successfully!')
                ->acceptDialog()
                ->logout();
        });

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
        $this->assertFalse(Hash::check($oldPassword, $user->fresh()->password));
    }
}