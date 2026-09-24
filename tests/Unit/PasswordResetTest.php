<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::firstOrCreate(['name' => 'attendee']);

        $this->user = User::factory()->create([
            'email' => 'wasimchai@gmail.com',
            'password' => Hash::make('789456'),
            'role_id' => $this->role->id,
            'status' => 'active',
        ]);
    }

    // Send Reset Link Tests 


    public function test_send_reset_link_successfully_for_valid_user(): void
    {
        Notification::fake();

        $response = $this->postJson('/forgot-password', [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => __(Password::RESET_LINK_SENT),
            ]);

        Notification::assertSentTo(
            $this->user,
            ResetPassword::class
        );
    }

    public function test_send_reset_link_fails_if_email_is_missing(): void
    {
        $response = $this->postJson('/forgot-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_send_reset_link_fails_if_email_format_is_invalid(): void
    {
        $response = $this->postJson('/forgot-password', [
            'email' => 'wasimchai',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_send_reset_link_returns_422_for_non_existent_email(): void
    {
        $response = $this->postJson('/forgot-password', [
            'email' => 'wasimchai@admin.com',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => __(Password::INVALID_USER),
            ]);
    }
    //Reset Password Tests

    public function test_user_can_reset_password_with_valid_token(): void
    {
        // Generate valid reset token for the user
        $token = Password::broker()->createToken($this->user);

        $newPassword = 'wasimMohammed';

        $response = $this->postJson('/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password reset successfully.',
            ]);

        // Verify password was updated in the database
        $this->user->refresh();
        $this->assertTrue(Hash::check($newPassword, $this->user->password));
    }

    public function test_reset_password_validates_required_fields(): void
    {
        $response = $this->postJson('/reset-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    public function test_reset_password_fails_if_email_format_is_invalid(): void
    {
        $token = Password::broker()->createToken($this->user);

        $response = $this->postJson('/reset-password', [
            'token' => $token,
            'email' => 'invalid-email',
            'password' => '741852',
            'password_confirmation' => '741852',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_reset_password_fails_if_password_confirmation_does_not_match(): void
    {
        $token = Password::broker()->createToken($this->user);

        $response = $this->postJson('/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => '789456',
            'password_confirmation' => '456123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_password_fails_if_password_is_shorter_than_minimum_length(): void
    {
        $token = Password::broker()->createToken($this->user);

        $response = $this->postJson('/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => '78945',
            'password_confirmation' => '78945',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $response = $this->postJson('/reset-password', [
            'token' => 'invalid-token-string',
            'email' => $this->user->email,
            'password' => '741852',
            'password_confirmation' => '741852',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => __(Password::INVALID_TOKEN),
            ]);
    }

    public function test_reset_password_fails_with_non_existent_user_email(): void
    {
        $token = Password::broker()->createToken($this->user);

        $response = $this->postJson('/reset-password', [
            'token' => $token,
            'email' => 'chaiaurcode@youtube.com',
            'password' => '789456',
            'password_confirmation' => '789456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => __(Password::INVALID_USER),
            ]);
    }
}
