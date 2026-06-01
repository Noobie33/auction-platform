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

    // =========================================================
    // Phase 5.4 — Approve / Reject
    // =========================================================

    // ── helpers ───────────────────────────────────────────────

    private function createAdmin(): User
    {
        return $this->makeUser('admin');
    }

    private function createBidderWithPendingApplication(): array
    {
        $applicant = $this->makeUser('bidder');

        $application = SellerRequest::create([
            'user_id'    => $applicant->id,
            'motivation' => 'I have several years of experience selling products online and want to become a verified seller on this platform.',
            'status'     => 'pending',
        ]);

        return [$applicant, $application];
    }

    // ── approve: happy path ───────────────────────────────────

    
    public function test_admin_can_approve_a_pending_application(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.approve', $application))
            ->assertRedirect(route('admin.seller-applications.index'))
            ->assertSessionHas('success');

        $this->assertSame('approved', $application->fresh()->status);
    }

  
    public function test_approve_assigns_seller_role_to_applicant(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.approve', $application));

        $this->assertTrue($applicant->fresh()->hasRole('seller'));
    }

    
    public function test_approve_removes_bidder_role_from_applicant(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.approve', $application));

        $this->assertFalse($applicant->fresh()->hasRole('bidder'));
    }

    
    public function test_approve_stores_reviewed_by_and_reviewed_at(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.seller-applications.approve', $application));

        $fresh = $application->fresh();

        $this->assertSame($admin->id, $fresh->reviewed_by);
        $this->assertNotNull($fresh->reviewed_at);
    }

    // ── reject: happy path ────────────────────────────────────

   
    public function test_admin_can_reject_a_pending_application_with_admin_note(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.reject', $application), [
                'admin_note' => 'Insufficient supporting documents.',
            ])
            ->assertRedirect(route('admin.seller-applications.index'))
            ->assertSessionHas('success');

        $this->assertSame('rejected', $application->fresh()->status);
    }

   
    public function test_reject_keeps_bidder_role_and_does_not_assign_seller(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.reject', $application), [
                'admin_note' => 'Does not meet requirements.',
            ]);

        $fresh = $applicant->fresh();

        $this->assertTrue($fresh->hasRole('bidder'));
        $this->assertFalse($fresh->hasRole('seller'));
    }


    public function test_reject_stores_admin_note_reviewed_by_and_reviewed_at(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.seller-applications.reject', $application), [
                'admin_note' => 'Missing valid ID.',
            ]);

        $fresh = $application->fresh();

        $this->assertSame('Missing valid ID.', $fresh->admin_note);
        $this->assertSame($admin->id, $fresh->reviewed_by);
        $this->assertNotNull($fresh->reviewed_at);
    }

    // ── guard: non-pending applications ──────────────────────

  
    public function test_cannot_approve_an_already_approved_application(): void
    {
        $applicant = $this->makeUser('bidder');
        $application = SellerRequest::create([
            'user_id' => $applicant->id,
            'motivation' => 'I have several years of experience selling products online and want to become a verified seller on this platform.',
            'status'  => 'approved',
        ]);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.approve', $application))
            ->assertStatus(422);
    }

    
    public function test_cannot_approve_an_already_rejected_application(): void
    {
        $applicant = $this->makeUser('bidder');
        $application = SellerRequest::create([
            'user_id' => $applicant->id,
            'motivation' => 'I have several years of experience selling products online and want to become a verified seller on this platform.',
            'status'  => 'rejected',
        ]);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.approve', $application))
            ->assertStatus(422);
    }


    public function test_cannot_reject_an_already_approved_application(): void
    {
        $applicant = $this->makeUser('bidder');
        $application = SellerRequest::create([
            'user_id' => $applicant->id,
            'motivation' => 'I have several years of experience selling products online and want to become a verified seller on this platform.',
            'status'  => 'approved',
        ]);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.reject', $application), [
                'admin_note' => 'Late rejection.',
            ])
            ->assertStatus(422);
    }

   
    public function test_cannot_reject_an_already_rejected_application(): void
    {
        $applicant = $this->makeUser('bidder');
        $application = SellerRequest::create([
            'user_id' => $applicant->id,
            'motivation' => 'I have several years of experience selling products online and want to become a verified seller on this platform.',
            'status'  => 'rejected',
        ]);

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.reject', $application), [
                'admin_note' => 'Duplicate rejection.',
            ])
            ->assertStatus(422);
    }

    // ── guard: authorisation ──────────────────────────────────

    
    public function test_non_admin_cannot_approve_an_application(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $nonAdmin = $this->makeUser('bidder');

        $this->actingAs($nonAdmin)
            ->post(route('admin.seller-applications.approve', $application))
            ->assertForbidden();
    }

   
    public function test_non_admin_cannot_reject_an_application(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $nonAdmin = $this->makeUser('bidder');

        $this->actingAs($nonAdmin)
            ->post(route('admin.seller-applications.reject', $application), [
                'admin_note' => 'Attempted by non-admin.',
            ])
            ->assertForbidden();
    }

    
    public function test_guest_is_redirected_to_login_when_approving(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $this->post(route('admin.seller-applications.approve', $application))
            ->assertRedirect(route('login'));
    }

  
    public function test_guest_is_redirected_to_login_when_rejecting(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $this->post(route('admin.seller-applications.reject', $application), [
            'admin_note' => 'Guest reject attempt.',
        ])->assertRedirect(route('login'));
    }

    // ── validation ────────────────────────────────────────────

   
    public function test_reject_requires_admin_note(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.reject', $application), [
                'admin_note' => '',
            ])
            ->assertSessionHasErrors('admin_note');

        $this->assertSame('pending', $application->fresh()->status);
    }

    
    public function test_reject_admin_note_must_not_exceed_1000_characters(): void
    {
        [$applicant, $application] = $this->createBidderWithPendingApplication();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.seller-applications.reject', $application), [
                'admin_note' => str_repeat('x', 1001),
            ])
            ->assertSessionHasErrors('admin_note');

        $this->assertSame('pending', $application->fresh()->status);
    }
}