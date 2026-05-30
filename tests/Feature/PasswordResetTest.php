<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function makeUser(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'status'            => 'active',
            'email_verified_at' => now(),
        ], $overrides));

        $user->assignRole('bidder');

        return $user;
    }

    // -------------------------------------------------------------------------
    // Forgot password page
    // -------------------------------------------------------------------------

    public function test_forgot_password_page_loads(): void
    {
        $this->get('/forgot-password')
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Send reset link
    // -------------------------------------------------------------------------

    public function test_reset_link_is_sent_for_existing_user(): void
    {
        Notification::fake();

        $user = $this->makeUser();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_link_request_fails_for_unknown_email(): void
    {
        $this->post('/forgot-password', ['email' => 'nobody@example.com'])
            ->assertSessionHasErrors('email');
    }

    // -------------------------------------------------------------------------
    // Reset password form
    // -------------------------------------------------------------------------

    public function test_reset_password_form_loads_with_valid_token(): void
    {
        $user  = $this->makeUser();
        $token = Password::broker()->createToken($user);

        $this->get("/reset-password/{$token}?email={$user->email}")
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Reset password update
    // -------------------------------------------------------------------------

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user  = $this->makeUser();
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertRedirect(route('dashboard'));

        // User should be authenticated immediately after a successful reset.
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        $user = $this->makeUser();

        $this->post('/reset-password', [
            'token'                 => 'invalid-token-string',
            'email'                 => $user->email,
            'password'              => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // Login with new password after reset
    // -------------------------------------------------------------------------

    public function test_user_can_login_with_new_password_after_reset(): void
    {
        $user  = $this->makeUser();
        $token = Password::broker()->createToken($user);

        // Reset the password — this also logs the user in.
        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        // Log out cleanly.
        $this->post('/logout');
        $this->assertGuest();

        // Attempt login with the new password.
        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'NewPassword1',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user->fresh());
    }
}