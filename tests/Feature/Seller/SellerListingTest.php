<?php

namespace Tests\Feature\Seller;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerListingTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        // One shared category covers all listing creation in this test file.
        // Category::create() is safe here — CategorySeeder is not called so
        // the slug namespace is empty at the start of every test.
        $this->category = Category::create([
            'name'      => 'Test Category',
            'slug'      => 'test-category',
            'is_active' => true,
        ]);
    }

    // ── Canonical user factory helper ──────────────────────────────────────────
    // Matches AuthRoleDashboardTest::makeUser() exactly.

    private function makeUser(string $role, array $overrides = []): User
    {
        $attributes = array_merge([
            'status'            => 'active',
            'email_verified_at' => now(),
        ], $overrides);

        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    // ── Listing factory helper ─────────────────────────────────────────────────
    // Uses uniqid() for the slug so multiple listings can be created within
    // the same test without triggering the unique constraint.

    private function createListing(User $seller, array $overrides = []): Listing
    {
        return Listing::create(array_merge([
            'seller_id'      => $seller->id,
            'category_id'    => $this->category->id,
            'title'          => 'Test Listing',
            'slug'           => 'test-listing-' . uniqid(),
            'description'    => 'A test listing description.',
            'starting_price' => '50.00',
            'condition'      => 'used',
            'status'         => 'draft',
        ], $overrides));
    }

    // ── Access control ─────────────────────────────────────────────────────────

    public function test_guest_is_redirected_from_seller_listings_index(): void
    {
        $this->get(route('seller.listings.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unverified_seller_is_redirected_to_verification_notice(): void
    {
        $seller = $this->makeUser('seller', ['email_verified_at' => null]);

        $this->actingAs($seller)
            ->get(route('seller.listings.index'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_bidder_cannot_access_seller_listings_index(): void
    {
        $bidder = $this->makeUser('bidder');

        $this->actingAs($bidder)
            ->get(route('seller.listings.index'))
            ->assertForbidden();
    }

    public function test_seller_can_access_seller_listings_index(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->get(route('seller.listings.index'))
            ->assertOk();
    }

    // ── Content isolation ──────────────────────────────────────────────────────

    public function test_seller_sees_only_their_own_listings(): void
    {
        $seller = $this->makeUser('seller');
        $this->createListing($seller, ['title' => 'My Own Listing']);

        $this->actingAs($seller)
            ->get(route('seller.listings.index'))
            ->assertSee('My Own Listing');
    }

    public function test_seller_does_not_see_another_sellers_listings(): void
    {
        $seller      = $this->makeUser('seller');
        $otherSeller = $this->makeUser('seller');

        $this->createListing($otherSeller, ['title' => 'Other Seller Listing']);

        $this->actingAs($seller)
            ->get(route('seller.listings.index'))
            ->assertDontSee('Other Seller Listing');
    }

    // ── Empty state ────────────────────────────────────────────────────────────

    public function test_empty_state_appears_when_seller_has_no_listings(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->get(route('seller.listings.index'))
            ->assertSee('You have not created any listings yet.');
    }

    // ── Dashboard navigation ───────────────────────────────────────────────────

    public function test_seller_dashboard_contains_my_listings_link(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->get(route('seller.dashboard'))
            ->assertSee(route('seller.listings.index'));
    }
}