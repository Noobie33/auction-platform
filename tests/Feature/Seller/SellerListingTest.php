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

    /**
     * Minimum valid POST data for a new listing.
     * Accepts overrides so individual validation tests can break one field at a time.
     */
    private function validListingData(array $overrides = []): array
    {
        return array_merge([
            'category_id'    => $this->category->id,
            'title'          => 'A Valid Listing Title',
            'description'    => 'This description is long enough to pass the twenty character minimum.',
            'condition'      => 'used',
            'starting_price' => '100.00',
        ], $overrides);
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

    // ── Create: access control ─────────────────────────────────────────────────

    public function test_seller_can_view_create_listing_page(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->get(route('seller.listings.create'))
            ->assertOk();
    }

    public function test_guest_is_redirected_from_create_listing_page(): void
    {
        $this->get(route('seller.listings.create'))
            ->assertRedirect(route('login'));
    }

    public function test_bidder_cannot_view_create_listing_page(): void
    {
        $bidder = $this->makeUser('bidder');

        $this->actingAs($bidder)
            ->get(route('seller.listings.create'))
            ->assertForbidden();
    }

    // ── Store: successful creation ─────────────────────────────────────────────

    public function test_seller_can_store_draft_listing(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), $this->validListingData())
            ->assertRedirect(route('seller.listings.index'));

        $this->assertDatabaseHas('listings', [
            'title'  => 'A Valid Listing Title',
            'status' => 'draft',
        ]);
    }

    public function test_stored_listing_belongs_to_authenticated_seller(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), $this->validListingData());

        $this->assertDatabaseHas('listings', [
            'seller_id' => $seller->id,
            'title'     => 'A Valid Listing Title',
        ]);
    }

    // ── Store: slug generation ─────────────────────────────────────────────────

    public function test_slug_is_generated_automatically(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), $this->validListingData([
                'title' => 'My Awesome Camera',
            ]));

        $this->assertDatabaseHas('listings', [
            'slug' => 'my-awesome-camera',
        ]);
    }

    public function test_duplicate_titles_generate_unique_slugs(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), $this->validListingData([
                'title' => 'Duplicate Listing Title',
            ]));

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), $this->validListingData([
                'title' => 'Duplicate Listing Title',
            ]));

        $this->assertDatabaseHas('listings', ['slug' => 'duplicate-listing-title']);
        $this->assertDatabaseHas('listings', ['slug' => 'duplicate-listing-title-2']);
    }

    // ── Store: validation failures ─────────────────────────────────────────────

    public function test_title_is_required(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), $this->validListingData(['title' => '']))
            ->assertSessionHasErrors('title');
    }

    public function test_category_is_required(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), $this->validListingData(['category_id' => '']))
            ->assertSessionHasErrors('category_id');
    }

    public function test_inactive_category_cannot_be_used(): void
    {
        $seller = $this->makeUser('seller');

        $inactive = Category::create([
            'name'      => 'Inactive Category',
            'slug'      => 'inactive-category',
            'is_active' => false,
        ]);

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), $this->validListingData([
                'category_id' => $inactive->id,
            ]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_description_minimum_validation_works(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), $this->validListingData([
                'description' => 'Too short.',
            ]))
            ->assertSessionHasErrors('description');
    }

    public function test_starting_price_is_required(): void
    {
        $seller = $this->makeUser('seller');

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), $this->validListingData([
                'starting_price' => '',
            ]))
            ->assertSessionHasErrors('starting_price');
    }

    // ── Edit: access control ───────────────────────────────────────────────────

    public function test_seller_can_view_edit_page_for_own_draft_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $this->actingAs($seller)
            ->get(route('seller.listings.edit', $listing))
            ->assertOk();
    }

    public function test_seller_cannot_view_edit_page_for_another_sellers_listing(): void
    {
        $seller      = $this->makeUser('seller');
        $otherSeller = $this->makeUser('seller');
        $listing     = $this->createListing($otherSeller);

        $this->actingAs($seller)
            ->get(route('seller.listings.edit', $listing))
            ->assertForbidden();
    }

    public function test_seller_cannot_edit_active_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'active']);

        $this->actingAs($seller)
            ->get(route('seller.listings.edit', $listing))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_edit_page(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $this->get(route('seller.listings.edit', $listing))
            ->assertRedirect(route('login'));
    }

    public function test_bidder_cannot_access_edit_page(): void
    {
        $seller  = $this->makeUser('seller');
        $bidder  = $this->makeUser('bidder');
        $listing = $this->createListing($seller);

        $this->actingAs($bidder)
            ->get(route('seller.listings.edit', $listing))
            ->assertForbidden();
    }

    // ── Update: successful update ──────────────────────────────────────────────

    public function test_seller_can_update_own_draft_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $this->actingAs($seller)
            ->patch(route('seller.listings.update', $listing), $this->validListingData([
                'title'       => 'Updated Listing Title',
                'description' => 'This is an updated description with enough characters.',
            ]))
            ->assertRedirect(route('seller.listings.index'));

        $this->assertDatabaseHas('listings', [
            'id'    => $listing->id,
            'title' => 'Updated Listing Title',
        ]);
    }

    public function test_update_keeps_listing_owned_by_same_seller(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $this->actingAs($seller)
            ->patch(route('seller.listings.update', $listing), $this->validListingData());

        $this->assertDatabaseHas('listings', [
            'id'        => $listing->id,
            'seller_id' => $seller->id,
        ]);
    }

    // ── Update: slug behaviour ─────────────────────────────────────────────────

    public function test_update_regenerates_slug_when_title_changes(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['slug' => 'old-slug']);

        $this->actingAs($seller)
            ->patch(route('seller.listings.update', $listing), $this->validListingData([
                'title' => 'Brand New Title Here',
            ]));

        $this->assertDatabaseHas('listings', [
            'id'   => $listing->id,
            'slug' => 'brand-new-title-here',
        ]);
    }

    public function test_update_with_duplicate_title_generates_unique_slug_excluding_current(): void
    {
        $seller  = $this->makeUser('seller');

        // listing holds 'original-title'
        $listing = $this->createListing($seller, [
            'title' => 'Original Title',
            'slug'  => 'original-title',
        ]);

        // A second listing already occupies the target slug
        $this->createListing($seller, [
            'title' => 'Collision Title',
            'slug'  => 'collision-title',
        ]);

        // Updating listing to 'Collision Title' must produce 'collision-title-2',
        // not 'collision-title'. The excludeId only protects listing's OWN slug
        // from blocking itself; the collision comes from the other listing.
        $this->actingAs($seller)
            ->patch(route('seller.listings.update', $listing), $this->validListingData([
                'title' => 'Collision Title',
            ]));

        $this->assertDatabaseHas('listings', [
            'id'   => $listing->id,
            'slug' => 'collision-title-2',
        ]);
    }

    // ── Update: validation failures ────────────────────────────────────────────

    public function test_title_is_required_on_update(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $this->actingAs($seller)
            ->patch(route('seller.listings.update', $listing), $this->validListingData([
                'title' => '',
            ]))
            ->assertSessionHasErrors('title');
    }

    public function test_inactive_category_cannot_be_used_on_update(): void
    {
        $seller   = $this->makeUser('seller');
        $listing  = $this->createListing($seller);
        $inactive = Category::create([
            'name'      => 'Inactive For Update',
            'slug'      => 'inactive-for-update',
            'is_active' => false,
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.update', $listing), $this->validListingData([
                'category_id' => $inactive->id,
            ]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_seller_cannot_update_another_sellers_listing(): void
    {
        $seller      = $this->makeUser('seller');
        $otherSeller = $this->makeUser('seller');

        $listing = $this->createListing($otherSeller, [
            'title' => 'Protected Other Seller Listing',
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.update', $listing), $this->validListingData([
                'title' => 'Attempted Hijack Title',
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('listings', [
            'id'    => $listing->id,
            'title' => 'Protected Other Seller Listing',
        ]);
    }

    public function test_seller_cannot_update_active_listing(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, [
            'title'  => 'Protected Active Listing',
            'status' => 'active',
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.update', $listing), $this->validListingData([
                'title' => 'Attempted Active Update',
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('listings', [
            'id'     => $listing->id,
            'title'  => 'Protected Active Listing',
            'status' => 'active',
        ]);
    }

    // ── Publish: access control ────────────────────────────────────────────────

    public function test_seller_can_publish_own_draft_listing_with_valid_ends_at(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'ends_at' => now()->addDays(7),
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.publish', $listing))
            ->assertRedirect(route('seller.listings.index'));
    }

    public function test_publishing_sets_status_to_active(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'ends_at' => now()->addDays(7),
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.publish', $listing));

        $this->assertDatabaseHas('listings', [
            'id'     => $listing->id,
            'status' => 'active',
        ]);
    }

    public function test_publishing_sets_starts_at_when_missing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'starts_at' => null,
            'ends_at'   => now()->addDays(7),
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.publish', $listing));

        $this->assertNotNull(Listing::find($listing->id)->starts_at);
    }

    public function test_seller_cannot_publish_another_sellers_listing(): void
    {
        $seller      = $this->makeUser('seller');
        $otherSeller = $this->makeUser('seller');
        $listing     = $this->createListing($otherSeller, [
            'ends_at' => now()->addDays(7),
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.publish', $listing))
            ->assertForbidden();
    }

    public function test_seller_cannot_publish_active_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'status'  => 'active',
            'ends_at' => now()->addDays(7),
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.publish', $listing))
            ->assertForbidden();
    }

    public function test_seller_cannot_publish_cancelled_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'status'  => 'cancelled',
            'ends_at' => now()->addDays(7),
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.publish', $listing))
            ->assertForbidden();
    }

    public function test_bidder_cannot_publish_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $bidder  = $this->makeUser('bidder');
        $listing = $this->createListing($seller, [
            'ends_at' => now()->addDays(7),
        ]);

        $this->actingAs($bidder)
            ->patch(route('seller.listings.publish', $listing))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_publish_route(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'ends_at' => now()->addDays(7),
        ]);

        $this->patch(route('seller.listings.publish', $listing))
            ->assertRedirect(route('login'));
    }

    // ── Publish: business rule guards ─────────────────────────────────────────

    public function test_publishing_without_ends_at_is_blocked_and_listing_stays_draft(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['ends_at' => null]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.publish', $listing))
            ->assertRedirect(route('seller.listings.index'));

        $this->assertDatabaseHas('listings', [
            'id'     => $listing->id,
            'status' => 'draft',
        ]);
    }

    public function test_publishing_with_past_ends_at_is_blocked_and_listing_stays_draft(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'ends_at' => now()->subDay(),
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.publish', $listing))
            ->assertRedirect(route('seller.listings.index'));

        $this->assertDatabaseHas('listings', [
            'id'     => $listing->id,
            'status' => 'draft',
        ]);
    }

    // ── Cancel: access control ─────────────────────────────────────────────────

    public function test_seller_can_cancel_own_active_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'active']);

        $this->actingAs($seller)
            ->patch(route('seller.listings.cancel', $listing))
            ->assertRedirect(route('seller.listings.index'));
    }

    public function test_cancelling_sets_status_to_cancelled(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'active']);

        $this->actingAs($seller)
            ->patch(route('seller.listings.cancel', $listing));

        $this->assertDatabaseHas('listings', [
            'id'     => $listing->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_seller_cannot_cancel_another_sellers_listing(): void
    {
        $seller      = $this->makeUser('seller');
        $otherSeller = $this->makeUser('seller');
        $listing     = $this->createListing($otherSeller, ['status' => 'active']);

        $this->actingAs($seller)
            ->patch(route('seller.listings.cancel', $listing))
            ->assertForbidden();
    }

    public function test_seller_cannot_cancel_draft_listing(): void
    {
        // Draft listings are not cancellable — isActive() returns false for draft.
        // Seller should delete a draft rather than cancel it (future unit).
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'draft']);

        $this->actingAs($seller)
            ->patch(route('seller.listings.cancel', $listing))
            ->assertForbidden();
    }

    public function test_seller_cannot_cancel_already_cancelled_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'cancelled']);

        $this->actingAs($seller)
            ->patch(route('seller.listings.cancel', $listing))
            ->assertForbidden();
    }

    public function test_bidder_cannot_cancel_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $bidder  = $this->makeUser('bidder');
        $listing = $this->createListing($seller, ['status' => 'active']);

        $this->actingAs($bidder)
            ->patch(route('seller.listings.cancel', $listing))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_cancel_route(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'active']);

        $this->patch(route('seller.listings.cancel', $listing))
            ->assertRedirect(route('login'));
    }

    // ── Index: action button visibility ───────────────────────────────────────

    public function test_index_shows_publish_button_for_draft_listing(): void
    {
        $seller = $this->makeUser('seller');
        $this->createListing($seller, ['status' => 'draft']);

        $this->actingAs($seller)
            ->get(route('seller.listings.index'))
            ->assertSee('Publish');
    }

    public function test_index_shows_edit_link_for_draft_listing(): void
    {
        $seller = $this->makeUser('seller');
        $this->createListing($seller, ['status' => 'draft']);

        $this->actingAs($seller)
            ->get(route('seller.listings.index'))
            ->assertSee('Edit');
    }

    public function test_index_shows_cancel_button_for_active_listing(): void
    {
        $seller = $this->makeUser('seller');
        $this->createListing($seller, ['status' => 'active']);

        $this->actingAs($seller)
            ->get(route('seller.listings.index'))
            ->assertSee('Cancel');
    }

    public function test_index_does_not_show_edit_link_for_active_listing(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, [
            'status' => 'active',
        ]);

        $this->actingAs($seller)
            ->get(route('seller.listings.index'))
            ->assertDontSee(route('seller.listings.edit', $listing));
    }

    public function test_seller_cannot_publish_ended_listing(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, [
            'status'  => 'ended',
            'ends_at' => now()->addDays(7),
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.publish', $listing))
            ->assertForbidden();
    }

    public function test_seller_cannot_publish_failed_listing(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, [
            'status'  => 'failed',
            'ends_at' => now()->addDays(7),
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.publish', $listing))
            ->assertForbidden();
    }

    public function test_seller_cannot_cancel_ended_listing(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, ['status' => 'ended']);

        $this->actingAs($seller)
            ->patch(route('seller.listings.cancel', $listing))
            ->assertForbidden();
    }

    public function test_seller_cannot_cancel_failed_listing(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, ['status' => 'failed']);

        $this->actingAs($seller)
            ->patch(route('seller.listings.cancel', $listing))
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Phase 7 · Unit 5 — Draft listing delete
    // -------------------------------------------------------------------------

    public function test_deleting_draft_listing_soft_deletes_the_record(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, [
            'status' => 'draft',
        ]);

        $this->actingAs($seller)
            ->delete(route('seller.listings.destroy', $listing));

        $this->assertSoftDeleted('listings', [
            'id' => $listing->id,
        ]);
    }

    public function test_guest_is_redirected_from_delete_route(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, [
            'status' => 'draft',
        ]);

        $this->delete(route('seller.listings.destroy', $listing))
            ->assertRedirect(route('login'));
    }

    public function test_seller_can_delete_own_draft_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'status' => 'draft',
            ]);

        $response = $this->actingAs($seller)
            ->delete(route('seller.listings.destroy', $listing));

        $response->assertRedirect(route('seller.listings.index'));
        $response->assertSessionHas('success');
    }


    public function test_seller_cannot_delete_another_sellers_draft_listing(): void
    {
        $seller      = $this->makeUser('seller');
        $otherSeller = $this->makeUser('seller');
        $listing     = $this->createListing($otherSeller, [
            'status' => 'draft',
        ]);

        $response = $this->actingAs($seller)
            ->delete(route('seller.listings.destroy', $listing));

        $response->assertForbidden();
    }

    public function test_seller_cannot_delete_active_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'status' => 'active',
        ]);

        $response = $this->actingAs($seller)
            ->delete(route('seller.listings.destroy', $listing));

        $response->assertForbidden();
    }

    public function test_seller_cannot_delete_cancelled_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($seller)
            ->delete(route('seller.listings.destroy', $listing));

        $response->assertForbidden();
    }

    public function test_seller_cannot_delete_ended_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'status' => 'ended',
        ]);

        $response = $this->actingAs($seller)
            ->delete(route('seller.listings.destroy', $listing));

        $response->assertForbidden();
    }

    public function test_seller_cannot_delete_failed_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'status' => 'failed',
        ]);

        $response = $this->actingAs($seller)
            ->delete(route('seller.listings.destroy', $listing));

        $response->assertForbidden();
    }

    public function test_bidder_cannot_delete_listing(): void
    {
        $bidder  = $this->makeUser('bidder');
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'status' => 'draft',
        ]);

        $response = $this->actingAs($bidder)
            ->delete(route('seller.listings.destroy', $listing));

        $response->assertForbidden();
    }

    public function test_index_shows_delete_button_for_draft_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, [
            'status' => 'draft',
        ]);

        $response = $this->actingAs($seller)
            ->get(route('seller.listings.index'));

        $response->assertSee(route('seller.listings.destroy', $listing));
    }

    public function test_index_does_not_show_delete_button_for_active_listing(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, [
            'status' => 'active',
        ]);

        $response = $this->actingAs($seller)
            ->get(route('seller.listings.index'));

        $response->assertDontSee('action="' . route('seller.listings.destroy', $listing) . '"', false);
    }

    public function test_deleted_listing_no_longer_appears_in_seller_listing_index(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, [
            'status' => 'draft',
            'title'  => 'Unique Draft To Delete',
        ]);

        $this->actingAs($seller)
            ->delete(route('seller.listings.destroy', $listing));

        $response = $this->actingAs($seller)
            ->get(route('seller.listings.index'));

        $response->assertSee('You have not created any listings yet.');
        $response->assertDontSee(route('seller.listings.edit', $listing));
        $response->assertDontSee(route('seller.listings.publish', $listing));
        $response->assertDontSee('action="' . route('seller.listings.destroy', $listing) . '"', false);
    }
}