<?php

namespace Tests\Feature\Auction;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionBrowseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        return $user;
    }

    private function makeCategory(string $name = 'Auction Category'): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => Category::generateSlug($name),
            'description' => 'Test category for auction browsing.',
            'parent_id' => null,
            'is_active' => true,
        ]);
    }

    private function makeListing(User $seller, Category $category, array $overrides = []): Listing
    {
        $title = $overrides['title'] ?? 'Test Auction Listing';

        return Listing::create(array_merge([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => $title,
            'slug' => Listing::generateSlug($title),
            'description' => 'This is a test auction listing description for public browse testing.',
            'condition' => 'new',
            'location' => 'Dhaka',
            'starting_price' => 1000,
            'reserve_price' => null,
            'current_bid' => null,
            'min_bid_increment' => 100,
            'buy_now_price' => null,
            'status' => 'active',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(7),
            'auto_extend' => false,
            'is_featured' => false,
            'view_count' => 0,
            'winner_id' => null,
        ], $overrides));
    }

    public function test_guest_can_access_auction_index(): void
    {
        $response = $this->get(route('auctions.index'));

        $response->assertOk();
    }

    public function test_authenticated_bidder_can_access_auction_index(): void
    {
        $bidder = $this->makeUser('bidder');

        $response = $this->actingAs($bidder)->get(route('auctions.index'));

        $response->assertOk();
    }

    public function test_authenticated_seller_can_access_auction_index(): void
    {
        $seller = $this->makeUser('seller');

        $response = $this->actingAs($seller)->get(route('auctions.index'));

        $response->assertOk();
    }

    public function test_authenticated_admin_can_access_auction_index(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->get(route('auctions.index'));

        $response->assertOk();
    }

    public function test_active_listing_appears_on_auction_index(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        $listing = $this->makeListing($seller, $category, [
            'title' => 'Visible Active Auction',
        ]);

        $response = $this->get(route('auctions.index'));

        $response->assertSee($listing->title);
    }

    public function test_draft_listing_does_not_appear_on_auction_index(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        $listing = $this->makeListing($seller, $category, [
            'status' => 'draft',
            'title' => 'Hidden Draft Auction',
        ]);

        $response = $this->get(route('auctions.index'));

        $response->assertDontSee($listing->title);
    }

    public function test_cancelled_listing_does_not_appear_on_auction_index(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        $listing = $this->makeListing($seller, $category, [
            'status' => 'cancelled',
            'title' => 'Hidden Cancelled Auction',
        ]);

        $response = $this->get(route('auctions.index'));

        $response->assertDontSee($listing->title);
    }

    public function test_ended_listing_does_not_appear_on_auction_index(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        $listing = $this->makeListing($seller, $category, [
            'status' => 'ended',
            'title' => 'Hidden Ended Auction',
        ]);

        $response = $this->get(route('auctions.index'));

        $response->assertDontSee($listing->title);
    }

    public function test_failed_listing_does_not_appear_on_auction_index(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        $listing = $this->makeListing($seller, $category, [
            'status' => 'failed',
            'title' => 'Hidden Failed Auction',
        ]);

        $response = $this->get(route('auctions.index'));

        $response->assertDontSee($listing->title);
    }

    public function test_listings_from_multiple_sellers_appear_on_auction_index(): void
    {
        $category = $this->makeCategory();

        $sellerA = $this->makeUser('seller');
        $sellerB = $this->makeUser('seller');

        $listingA = $this->makeListing($sellerA, $category, [
            'title' => 'Seller A Public Auction',
        ]);

        $listingB = $this->makeListing($sellerB, $category, [
            'title' => 'Seller B Public Auction',
        ]);

        $response = $this->get(route('auctions.index'));

        $response->assertSee($listingA->title);
        $response->assertSee($listingB->title);
    }

    public function test_listing_with_primary_image_shows_image_path(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();
        $listing = $this->makeListing($seller, $category, [
            'title' => 'Auction With Image',
        ]);

        $imagePath = 'listings/' . $listing->id . '/primary-test.jpg';

        ListingImage::create([
            'listing_id' => $listing->id,
            'image_path' => $imagePath,
            'alt_text' => null,
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $response = $this->get(route('auctions.index'));

        $response->assertSee($imagePath);
    }

    public function test_listing_without_image_does_not_error_and_shows_placeholder(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        $this->makeListing($seller, $category, [
            'title' => 'Auction Without Image',
        ]);

        $response = $this->get(route('auctions.index'));

        $response->assertOk();
        $response->assertSee('No image available');
    }

    public function test_pagination_limits_first_page_to_twelve_listings(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        for ($i = 1; $i <= 13; $i++) {
            $number = str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            $this->makeListing($seller, $category, [
                'title' => 'Auction Page Item ' . $number,
                'ends_at' => now()->addDays($i),
            ]);
        }

        $response = $this->get(route('auctions.index'));

        $response->assertOk();

        for ($i = 1; $i <= 12; $i++) {
            $number = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $response->assertSee('Auction Page Item ' . $number);
        }

        $response->assertDontSee('Auction Page Item 13');
        $response->assertSee('page=2');
    }

    public function test_empty_state_appears_when_there_are_no_active_listings(): void
    {
        $response = $this->get(route('auctions.index'));

        $response->assertSee('No active auctions');
    }

    public function test_keyword_search_matches_listing_title(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        $listing = $this->makeListing($seller, $category, [
            'title' => 'Vintage Mechanical Watch',
        ]);

        $response = $this->get(route('auctions.index', [
            'q' => 'Mechanical',
        ]));

        $response->assertSee($listing->title);
    }

    public function test_keyword_search_matches_listing_description(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        $listing = $this->makeListing($seller, $category, [
            'title' => 'Rare Collectible Item',
            'description' => 'A unique handcrafted wooden sculpture.',
        ]);

        $response = $this->get(route('auctions.index', [
            'q' => 'handcrafted',
        ]));

        $response->assertSee($listing->title);
    }

    public function test_keyword_search_hides_non_matching_active_listings(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        $matching = $this->makeListing($seller, $category, [
            'title' => 'Red Vintage Bicycle',
        ]);

        $nonMatching = $this->makeListing($seller, $category, [
            'title' => 'Blue Modern Laptop',
        ]);

        $response = $this->get(route('auctions.index', [
            'q' => 'Vintage',
        ]));

        $response->assertSee($matching->title);
        $response->assertDontSee($nonMatching->title);
    }

    public function test_keyword_search_with_no_match_shows_empty_state(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory();

        $this->makeListing($seller, $category, [
            'title' => 'Common Auction Item',
        ]);

        $response = $this->get(route('auctions.index', [
            'q' => 'xyznonexistentkeywordZZZ',
        ]));

        $response->assertOk();
        $response->assertSee('No listings match your search');
    }

    public function test_category_filter_shows_listings_from_selected_active_category(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory('Selected Category');

        $listing = $this->makeListing($seller, $category, [
            'title' => 'Selected Category Auction',
        ]);

        $response = $this->get(route('auctions.index', [
            'category' => $category->id,
        ]));

        $response->assertSee($listing->title);
    }

    public function test_category_filter_hides_listings_from_other_categories(): void
    {
        $seller = $this->makeUser('seller');

        $categoryA = $this->makeCategory('Category A');
        $categoryB = $this->makeCategory('Category B');

        $listingA = $this->makeListing($seller, $categoryA, [
            'title' => 'Category A Item',
        ]);

        $listingB = $this->makeListing($seller, $categoryB, [
            'title' => 'Category B Item',
        ]);

        $response = $this->get(route('auctions.index', [
            'category' => $categoryA->id,
        ]));

        $response->assertSee($listingA->title);
        $response->assertDontSee($listingB->title);
    }

    public function test_category_dropdown_shows_active_categories(): void
    {
        $category = $this->makeCategory('Visible Active Category');

        $response = $this->get(route('auctions.index'));

        $response->assertSee($category->name);
    }

    public function test_category_dropdown_does_not_show_inactive_categories(): void
    {
        $inactive = Category::create([
            'name' => 'InactiveCategoryXQ9',
            'slug' => Category::generateSlug('InactiveCategoryXQ9'),
            'description' => 'Inactive category should not appear in public filter.',
            'parent_id' => null,
            'is_active' => false,
        ]);

        $response = $this->get(route('auctions.index'));

        $response->assertDontSee($inactive->name);
    }

    public function test_invalid_category_filter_does_not_error_and_shows_empty_state(): void
    {
        $response = $this->get(route('auctions.index', [
            'category' => 99999,
        ]));

        $response->assertOk();
        $response->assertSee('No listings match your search');
    }

    public function test_search_and_category_filter_can_work_together(): void
    {
        $seller = $this->makeUser('seller');

        $categoryA = $this->makeCategory('Instrument Category');
        $categoryB = $this->makeCategory('Audio Category');

        $match = $this->makeListing($seller, $categoryA, [
            'title' => 'Vintage Guitar',
        ]);

        $wrongCategory = $this->makeListing($seller, $categoryB, [
            'title' => 'Guitar Amplifier',
        ]);

        $wrongKeyword = $this->makeListing($seller, $categoryA, [
            'title' => 'Piano Keys',
            'description' => 'Classic keyboard instrument.',
        ]);

        $response = $this->get(route('auctions.index', [
            'q' => 'guitar',
            'category' => $categoryA->id,
        ]));

        $response->assertSee($match->title);
        $response->assertDontSee($wrongCategory->title);
        $response->assertDontSee($wrongKeyword->title);
    }

    public function test_pagination_keeps_query_string_across_pages(): void
    {
        $seller = $this->makeUser('seller');
        $category = $this->makeCategory('Widget Category');

        for ($i = 1; $i <= 13; $i++) {
            $this->makeListing($seller, $category, [
                'title' => 'Widget Item ' . $i,
                'ends_at' => now()->addDays($i),
            ]);
        }

        $response = $this->get(route('auctions.index', [
            'q' => 'widget',
            'category' => $category->id,
        ]));

        $response->assertOk();
        $response->assertSee('page=2');
        $response->assertSee('q=widget');
        $response->assertSee('category=' . $category->id);
    }
}