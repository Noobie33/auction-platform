<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminUserManagementTest extends TestCase
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

    private function makeUser(string $role, array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'status'            => 'active',
            'email_verified_at' => now(),
        ], $overrides));

        $user->assignRole($role);

        return $user;
    }

    // -------------------------------------------------------------------------
    // Guest access — user list
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_from_user_list_to_login(): void
    {
        $this->get('/admin/users')
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Unverified admin — user list
    // -------------------------------------------------------------------------

    public function test_unverified_admin_is_redirected_to_verification_notice(): void
    {
        $admin = $this->makeUser('admin', ['email_verified_at' => null]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertRedirect(route('verification.notice'));
    }

    // -------------------------------------------------------------------------
    // Non-admin access — user list
    // -------------------------------------------------------------------------

    public function test_non_admin_verified_user_cannot_access_user_list(): void
    {
        $bidder = $this->makeUser('bidder');

        $this->actingAs($bidder)
            ->get('/admin/users')
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Admin access — user list
    // -------------------------------------------------------------------------

    public function test_admin_can_view_user_list(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // User list content
    // -------------------------------------------------------------------------

    public function test_user_list_displays_user_names_and_emails(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder');

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee($bidder->name)
            ->assertSee($bidder->email);
    }

    public function test_user_list_displays_user_roles(): void
    {
        $admin  = $this->makeUser('admin');
        $seller = $this->makeUser('seller');

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('seller');
    }

    // =========================================================================
    // Status update — guest and access control
    // =========================================================================

    public function test_guest_is_redirected_from_status_update_to_login(): void
    {
        $bidder = $this->makeUser('bidder');

        $this->patch("/admin/users/{$bidder->id}/status", ['status' => 'suspended'])
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_update_user_status(): void
    {
        $bidder = $this->makeUser('bidder');
        $target = $this->makeUser('bidder');

        $this->actingAs($bidder)
            ->patch("/admin/users/{$target->id}/status", ['status' => 'suspended'])
            ->assertStatus(403);
    }

    // =========================================================================
    // Status update — valid actions
    // =========================================================================

    public function test_admin_can_suspend_a_user(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder');

        $this->actingAs($admin)
            ->patch("/admin/users/{$bidder->id}/status", ['status' => 'suspended'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id'     => $bidder->id,
            'status' => 'suspended',
        ]);
    }

    public function test_admin_can_ban_a_user(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder');

        $this->actingAs($admin)
            ->patch("/admin/users/{$bidder->id}/status", ['status' => 'banned'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id'     => $bidder->id,
            'status' => 'banned',
        ]);
    }

    public function test_admin_can_reactivate_a_suspended_user(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder', ['status' => 'suspended']);

        $this->actingAs($admin)
            ->patch("/admin/users/{$bidder->id}/status", ['status' => 'active'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id'     => $bidder->id,
            'status' => 'active',
        ]);
    }

    // =========================================================================
    // Status update — guards
    // =========================================================================

    public function test_admin_cannot_update_their_own_status(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->patch("/admin/users/{$admin->id}/status", ['status' => 'suspended'])
            ->assertStatus(403);
    }

    public function test_admin_cannot_update_another_admin_status(): void
    {
        $admin       = $this->makeUser('admin');
        $otherAdmin  = $this->makeUser('admin');

        $this->actingAs($admin)
            ->patch("/admin/users/{$otherAdmin->id}/status", ['status' => 'suspended'])
            ->assertStatus(403);
    }

    // =========================================================================
    // Status update — validation
    // =========================================================================

    public function test_invalid_status_value_is_rejected(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder');

        $this->actingAs($admin)
            ->patch("/admin/users/{$bidder->id}/status", ['status' => 'deleted'])
            ->assertSessionHasErrors('status');
    }
}