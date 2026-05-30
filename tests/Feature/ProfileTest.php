<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase
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
    // Guest access
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_from_profile_to_login(): void
    {
        $this->get('/profile')
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Unverified user
    // -------------------------------------------------------------------------

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $user = $this->makeUser(['email_verified_at' => null]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertRedirect(route('verification.notice'));
    }

    // -------------------------------------------------------------------------
    // Profile view
    // -------------------------------------------------------------------------

    public function test_verified_user_can_view_profile_page(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Profile update
    // -------------------------------------------------------------------------

    public function test_verified_user_can_update_profile(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->patch('/profile', [
                'name'     => 'Updated Name',
                'username' => 'updateduser',
                'phone'    => '+8801712345678',
                'bio'      => 'This is my updated bio.',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'name'     => 'Updated Name',
            'username' => 'updateduser',
            'phone'    => '+8801712345678',
            'bio'      => 'This is my updated bio.',
        ]);
    }

    // -------------------------------------------------------------------------
    // Username uniqueness
    // -------------------------------------------------------------------------

    public function test_user_can_save_profile_with_their_own_existing_username(): void
    {
        $user = $this->makeUser(['username' => 'myusername']);

        // Re-submitting the same username must not trigger a unique constraint error.
        $this->actingAs($user)
            ->patch('/profile', [
                'name'     => $user->name,
                'username' => 'myusername',
            ])
            ->assertRedirect(route('profile.edit'));
    }

    public function test_username_must_be_unique_across_users(): void
    {
        $this->makeUser(['username' => 'takenusername']);
        $user = $this->makeUser(['username' => 'myusername']);

        $this->actingAs($user)
            ->patch('/profile', [
                'name'     => $user->name,
                'username' => 'takenusername',
            ])
            ->assertSessionHasErrors('username');
    }

    // -------------------------------------------------------------------------
    // Email cannot be changed
    // -------------------------------------------------------------------------

    public function test_email_cannot_be_changed_through_profile_update(): void
    {
        $user = $this->makeUser(['email' => 'original@example.com']);

        // Submit email as part of the request body.
        // UpdateProfileRequest does not declare email in its rules,
        // so validated() will never include it and update() ignores it.
        $this->actingAs($user)
            ->patch('/profile', [
                'name'  => $user->name,
                'email' => 'attacker@example.com',
            ]);

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'email' => 'original@example.com',
        ]);
    }
}