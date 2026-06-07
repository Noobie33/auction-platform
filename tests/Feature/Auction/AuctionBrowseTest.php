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
}