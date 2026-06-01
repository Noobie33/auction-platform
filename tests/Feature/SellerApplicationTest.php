<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SellerRequest;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerApplicationTest extends TestCase
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
            'motivation' => 'I have several years of experience selling goods online and would like to use this platform.',
            'status'     => $status,
        ]);
    }

    // -------------------------------------------------------------------------
    // Guest access
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_from_apply_page_to_login(): void
    {
        $this->get('/seller/apply')
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_status_page_to_login(): void
    {
        $this->get('/seller/application/status')
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Unverified user
    // -------------------------------------------------------------------------

    public function test_unverified_bidder_is_redirected_to_verification_notice(): void
    {
        $user = $this->makeUser('bidder', ['email_verified_at' => null]);

        $this->actingAs($user)
            ->get('/seller/apply')
            ->assertRedirect(route('verification.notice'));
    }

    // -------------------------------------------------------------------------
    // Non-bidder access
    // -------------------------------------------------------------------------

    public function test_non_bidder_cannot_access_apply_page(): void
    {
        $user = $this->makeUser('admin');

        $this->actingAs($user)
            ->get('/seller/apply')
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Apply form
    // -------------------------------------------------------------------------

    public function test_bidder_can_view_application_form(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->get('/seller/apply')
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Application submission
    // -------------------------------------------------------------------------

    public function test_bidder_can_submit_valid_application(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->post('/seller/apply', [
                'motivation' => 'I have several years of experience selling handmade goods and would love to join this platform.',
            ])
            ->assertRedirect(route('seller.application.status'));

        $this->assertDatabaseHas('seller_requests', [
            'user_id' => $user->id,
            'status'  => 'pending',
        ]);
    }

    public function test_bidder_with_existing_application_is_redirected_from_apply_to_status(): void
    {
        $user = $this->makeUser('bidder');
        $this->makeApplication($user);

        $this->actingAs($user)
            ->get('/seller/apply')
            ->assertRedirect(route('seller.application.status'));
    }

    public function test_bidder_cannot_submit_duplicate_application(): void
    {
        $user = $this->makeUser('bidder');
        $this->makeApplication($user);

        $this->actingAs($user)
            ->post('/seller/apply', [
                'motivation' => 'I have several years of experience selling handmade goods and would love to join this platform.',
            ])
            ->assertRedirect(route('seller.application.status'));

        // The original application must be the only record for this user.
        $this->assertDatabaseCount('seller_requests', 1);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_motivation_is_required(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->post('/seller/apply', ['motivation' => ''])
            ->assertSessionHasErrors('motivation');
    }

    public function test_motivation_must_be_at_least_50_characters(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->post('/seller/apply', ['motivation' => str_repeat('a', 49)])
            ->assertSessionHasErrors('motivation');
    }

    // -------------------------------------------------------------------------
    // Status page
    // -------------------------------------------------------------------------

    public function test_bidder_can_view_application_status_page(): void
    {
        $user = $this->makeUser('bidder');
        $this->makeApplication($user);

        $this->actingAs($user)
            ->get('/seller/application/status')
            ->assertOk()
            ->assertSee('Seller Application Status');
    }

    public function test_bidder_without_application_is_redirected_from_status_to_apply(): void
    {
        $user = $this->makeUser('bidder');

        $this->actingAs($user)
            ->get('/seller/application/status')
            ->assertRedirect(route('seller.apply'));
    }
}