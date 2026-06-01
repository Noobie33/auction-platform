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
    // Guest access
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_from_user_list_to_login(): void
    {
        $this->get('/admin/users')
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Unverified admin
    // -------------------------------------------------------------------------

    public function test_unverified_admin_is_redirected_to_verification_notice(): void
    {
        $admin = $this->makeUser('admin', ['email_verified_at' => null]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertRedirect(route('verification.notice'));
    }

    // -------------------------------------------------------------------------
    // Non-admin access
    // -------------------------------------------------------------------------

    public function test_non_admin_verified_user_cannot_access_user_list(): void
    {
        $bidder = $this->makeUser('bidder');

        $this->actingAs($bidder)
            ->get('/admin/users')
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Admin access
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
}