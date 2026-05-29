<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class AuthRoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    // -------------------------------------------------------------------------
    // Helper — always sets status explicitly so DB default is never relied upon
    // -------------------------------------------------------------------------

    private function makeUser(string $role, array $overrides = []): User
    {
        $attributes = array_merge(['status' => 'active'], $overrides);
        $user = User::factory()->create($attributes);
        $user->assignRole($role);
        return $user;
    }

    // -------------------------------------------------------------------------
    // Guest access
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_admin_dashboard_to_login(): void
    {
        $this->get('/admin/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_seller_dashboard_to_login(): void
    {
        $this->get('/seller/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_bidder_dashboard_to_login(): void
    {
        $this->get('/bidder/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_staff_dashboard_to_login(): void
    {
        $this->get('/staff/dashboard')
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Authenticated bidder — smart redirect from /dashboard
    // -------------------------------------------------------------------------

    public function test_authenticated_bidder_visiting_dashboard_is_redirected_to_bidder_dashboard(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('bidder.dashboard'));
    }

    // -------------------------------------------------------------------------
    // Authenticated bidder — own dashboard
    // -------------------------------------------------------------------------

    public function test_authenticated_bidder_can_access_bidder_dashboard(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->get('/bidder/dashboard')
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Authenticated bidder — forbidden dashboards return 403
    // -------------------------------------------------------------------------

    public function test_authenticated_bidder_cannot_access_admin_dashboard(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_authenticated_bidder_cannot_access_seller_dashboard(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->get('/seller/dashboard')
            ->assertStatus(403);
    }

    public function test_authenticated_bidder_cannot_access_staff_dashboard(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->get('/staff/dashboard')
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Role-specific dashboard access
    // -------------------------------------------------------------------------

    public function test_admin_can_access_admin_dashboard(): void
    {
        $user = $this->makeUser('admin');

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_seller_can_access_seller_dashboard(): void
    {
        $user = $this->makeUser('seller');

        $this->actingAs($user)
            ->get('/seller/dashboard')
            ->assertOk();
    }

    public function test_bidder_can_access_bidder_dashboard(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->get('/bidder/dashboard')
            ->assertOk();
    }

    public function test_staff_can_access_staff_dashboard(): void
    {
        $user = $this->makeUser('staff');

        $this->actingAs($user)
            ->get('/staff/dashboard')
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Suspended account
    // -------------------------------------------------------------------------

    public function test_suspended_user_cannot_access_dashboard(): void
    {
        $user = $this->makeUser('bidder', ['status' => 'suspended']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_suspended_user_cannot_access_role_dashboard_directly(): void
    {
        $user = $this->makeUser('bidder', ['status' => 'suspended']);

        $this->actingAs($user)
            ->get('/bidder/dashboard')
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Banned account
    // -------------------------------------------------------------------------

    public function test_banned_user_cannot_access_dashboard(): void
    {
        $user = $this->makeUser('bidder', ['status' => 'banned']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_banned_user_cannot_access_role_dashboard_directly(): void
    {
        $user = $this->makeUser('bidder', ['status' => 'banned']);

        $this->actingAs($user)
            ->get('/bidder/dashboard')
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));
    }

    public function test_user_is_unauthenticated_after_logout(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }
}