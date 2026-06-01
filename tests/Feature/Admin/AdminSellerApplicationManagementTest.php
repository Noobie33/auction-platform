<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\SellerRequest;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminSellerApplicationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
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

    private function makeApplication(User $user, string $status = 'pending'): SellerRequest
    {
        return SellerRequest::create([
            'user_id'    => $user->id,
            'motivation' => 'I have several years of experience selling goods online and would like to use this platform to grow my business.',
            'status'     => $status,
        ]);
    }

    // -------------------------------------------------------------------------
    // Guest access
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_from_seller_applications_to_login(): void
    {
        $this->get('/admin/seller-applications')
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Unverified admin
    // -------------------------------------------------------------------------

    public function test_unverified_admin_is_redirected_to_verification_notice(): void
    {
        $admin = $this->makeUser('admin', ['email_verified_at' => null]);

        $this->actingAs($admin)
            ->get('/admin/seller-applications')
            ->assertRedirect(route('verification.notice'));
    }

    // -------------------------------------------------------------------------
    // Non-admin access
    // -------------------------------------------------------------------------

    public function test_non_admin_cannot_access_seller_applications_list(): void
    {
        $bidder = $this->makeUser('bidder');

        $this->actingAs($bidder)
            ->get('/admin/seller-applications')
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Admin access
    // -------------------------------------------------------------------------

    public function test_admin_can_view_seller_applications_list(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get('/admin/seller-applications')
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Default filter shows pending
    // -------------------------------------------------------------------------

    public function test_default_filter_shows_pending_applications(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder');
        $this->makeApplication($bidder, 'pending');

        $this->actingAs($admin)
            ->get('/admin/seller-applications')
            ->assertOk()
            ->assertSee($bidder->name);
    }

    public function test_default_filter_hides_approved_applications(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder');
        $this->makeApplication($bidder, 'approved');

        $this->actingAs($admin)
            ->get('/admin/seller-applications')
            ->assertOk()
            ->assertDontSee($bidder->email);
    }

    public function test_default_filter_hides_rejected_applications(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder');
        $this->makeApplication($bidder, 'rejected');

        $this->actingAs($admin)
            ->get('/admin/seller-applications')
            ->assertOk()
            ->assertDontSee($bidder->email);
    }

    // -------------------------------------------------------------------------
    // Filter: approved
    // -------------------------------------------------------------------------

    public function test_filter_approved_shows_approved_applications(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder');
        $this->makeApplication($bidder, 'approved');

        $this->actingAs($admin)
            ->get('/admin/seller-applications?filter=approved')
            ->assertOk()
            ->assertSee($bidder->name);
    }

    public function test_filter_approved_hides_pending_applications(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder');
        $this->makeApplication($bidder, 'pending');

        $this->actingAs($admin)
            ->get('/admin/seller-applications?filter=approved')
            ->assertOk()
            ->assertDontSee($bidder->email);
    }

    // -------------------------------------------------------------------------
    // Filter: all
    // -------------------------------------------------------------------------

    public function test_filter_all_shows_all_applications(): void
    {
        $admin   = $this->makeUser('admin');
        $bidder1 = $this->makeUser('bidder');
        $bidder2 = $this->makeUser('bidder');
        $this->makeApplication($bidder1, 'pending');
        $this->makeApplication($bidder2, 'approved');

        $this->actingAs($admin)
            ->get('/admin/seller-applications?filter=all')
            ->assertOk()
            ->assertSee($bidder1->name)
            ->assertSee($bidder2->name);
    }

    // -------------------------------------------------------------------------
    // Invalid filter falls back to pending
    // -------------------------------------------------------------------------

    public function test_invalid_filter_falls_back_to_pending(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get('/admin/seller-applications?filter=invalid')
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Content: applicant info displayed
    // -------------------------------------------------------------------------

    public function test_applications_list_shows_applicant_name_and_email(): void
    {
        $admin  = $this->makeUser('admin');
        $bidder = $this->makeUser('bidder');
        $this->makeApplication($bidder, 'pending');

        $this->actingAs($admin)
            ->get('/admin/seller-applications')
            ->assertOk()
            ->assertSee($bidder->name)
            ->assertSee($bidder->email);
    }
}