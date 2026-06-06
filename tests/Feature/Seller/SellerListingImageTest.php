<?php

namespace Tests\Feature\Seller;

use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Category;
use Database\Seeders\RoleSeeder;

class SellerListingImageTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    // -----------------------------------------------------------------------
    // Bootstrap
    // -----------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Storage::fake('public');

        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    //
    // makeUser() mirrors the pattern used in SellerListingTest.
    // If makeUser() is already defined on Tests\TestCase, remove it here.
    // -----------------------------------------------------------------------

    private function makeUser(string $role): User
    {
        $user = User::factory()->create([
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createListing(User $seller, array $overrides = []): Listing
    {
        return Listing::create(array_merge([
            'seller_id' => $seller->id,
            'category_id' => $this->category->id,
            'title' => 'Test Listing',
            'slug' => 'test-listing-' . uniqid(),
            'description' => 'A valid test listing description.',
            'condition' => 'used',
            'starting_price' => '100.00',
            'status' => 'draft',
        ], $overrides));
    }

    /**
     * Create a ListingImage record and seed a corresponding fake file on the
     * public disk.  Uses direct Eloquent creation to avoid any factory gap.
     */
    private function makeListingImage(Listing $listing, array $attributes = []): ListingImage
    {
        $path = $attributes['image_path']
            ?? "listings/{$listing->id}/test-" . uniqid() . '.jpg';

        Storage::disk('public')->put($path, 'fake-image-content');

        return ListingImage::create(array_merge([
            'listing_id' => $listing->id,
            'image_path' => $path,
            'alt_text'   => null,
            'is_primary' => false,
            'sort_order' => 0,
        ], $attributes));
    }

    private function fakeImage(string $name = 'photo.png'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'test-image-');

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        );

        file_put_contents($path, $png);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function fakeOversizedImage(string $name = 'large.png'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'large-test-image-');

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        );

        file_put_contents($path, $png . str_repeat('a', 3 * 1024 * 1024));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    // -----------------------------------------------------------------------
    // Upload tests  (1 – 14)
    // -----------------------------------------------------------------------

    public function test_seller_can_upload_image_to_own_draft_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $response = $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage(),
            ]);

        $response->assertRedirect(route('seller.listings.edit', $listing));
        $response->assertSessionHas('success');
    }

    public function test_uploaded_image_file_is_stored_on_public_disk(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage(),
            ]);

        $record = ListingImage::where('listing_id', $listing->id)->firstOrFail();
        Storage::disk('public')->assertExists($record->image_path);
    }

    public function test_uploaded_image_path_is_persisted_in_listing_images(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage(),
            ]);

        $this->assertDatabaseHas('listing_images', ['listing_id' => $listing->id]);
    }

    public function test_first_uploaded_image_is_automatically_primary(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage('first.jpg'),
            ]);

        $image = ListingImage::where('listing_id', $listing->id)->firstOrFail();
        $this->assertTrue((bool) $image->is_primary);
    }

    public function test_second_uploaded_image_is_not_automatically_primary(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage('first.jpg'),
            ]);

        $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage('second.jpg'),
            ]);

        $second = ListingImage::where('listing_id', $listing->id)
            ->orderByDesc('sort_order')
            ->firstOrFail();

        $this->assertFalse((bool) $second->is_primary);
    }

    public function test_seller_cannot_upload_image_to_another_sellers_listing(): void
    {
        $seller      = $this->makeUser('seller');
        $otherSeller = $this->makeUser('seller');
        $listing     = $this->createListing($otherSeller);

        $response = $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage(),
            ]);

        $response->assertForbidden();
    }

    public function test_seller_cannot_upload_image_to_own_active_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'active']);

        $response = $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage(),
            ]);

        $response->assertForbidden();
    }

    public function test_seller_cannot_upload_image_to_own_cancelled_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'cancelled']);

        $response = $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage(),
            ]);

        $response->assertForbidden();
    }

    public function test_seller_cannot_upload_image_to_own_ended_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'ended']);

        $response = $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage(),
            ]);

        $response->assertForbidden();
    }

    public function test_seller_cannot_upload_image_to_own_failed_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'failed']);

        $response = $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage(),
            ]);

        $response->assertForbidden();
    }

    public function test_non_image_file_is_rejected(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $response = $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'),
            ]);

        $response->assertSessionHasErrors('image');
    }

    public function test_oversized_image_is_rejected(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $response = $this->actingAs($seller)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeOversizedImage(),
            ]);

        $response->assertSessionHasErrors('image');
    }

    public function test_bidder_cannot_upload_image(): void
    {
        $bidder  = $this->makeUser('bidder');
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $response = $this->actingAs($bidder)
            ->post(route('seller.listings.images.store', $listing), [
                'image' => $this->fakeImage(),
            ]);

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_from_upload_route(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $response = $this->post(route('seller.listings.images.store', $listing), [
            'image' => $this->fakeImage(),
        ]);

        $response->assertRedirect();
    }

    // -----------------------------------------------------------------------
    // Delete tests  (15 – 21)
    // -----------------------------------------------------------------------

    public function test_seller_can_delete_own_listing_image_from_draft_listing(): void
    {
        $seller       = $this->makeUser('seller');
        $listing      = $this->createListing($seller);
        $listingImage = $this->makeListingImage($listing);

        $response = $this->actingAs($seller)
            ->delete(route('seller.listings.images.destroy', [$listing, $listingImage]));

        $response->assertRedirect(route('seller.listings.edit', $listing));
        $response->assertSessionHas('success');
    }

    public function test_deleting_image_removes_file_from_public_disk(): void
    {
        $seller       = $this->makeUser('seller');
        $listing      = $this->createListing($seller);
        $listingImage = $this->makeListingImage($listing);
        $path         = $listingImage->image_path;

        $this->actingAs($seller)
            ->delete(route('seller.listings.images.destroy', [$listing, $listingImage]));

        Storage::disk('public')->assertMissing($path);
    }

    public function test_deleting_image_removes_record_from_listing_images(): void
    {
        $seller       = $this->makeUser('seller');
        $listing      = $this->createListing($seller);
        $listingImage = $this->makeListingImage($listing);

        $this->actingAs($seller)
            ->delete(route('seller.listings.images.destroy', [$listing, $listingImage]));

        $this->assertDatabaseMissing('listing_images', ['id' => $listingImage->id]);
    }

    public function test_seller_cannot_delete_another_sellers_listing_image(): void
    {
        $seller       = $this->makeUser('seller');
        $otherSeller  = $this->makeUser('seller');
        $listing      = $this->createListing($otherSeller);
        $listingImage = $this->makeListingImage($listing);

        $response = $this->actingAs($seller)
            ->delete(route('seller.listings.images.destroy', [$listing, $listingImage]));

        $response->assertForbidden();
    }

    public function test_seller_cannot_delete_image_from_own_active_listing(): void
    {
        $seller       = $this->makeUser('seller');
        $listing      = $this->createListing($seller, ['status' => 'active']);
        $listingImage = $this->makeListingImage($listing);

        $response = $this->actingAs($seller)
            ->delete(route('seller.listings.images.destroy', [$listing, $listingImage]));

        $response->assertForbidden();
    }

    public function test_seller_cannot_delete_image_from_own_cancelled_listing(): void
    {
        $seller       = $this->makeUser('seller');
        $listing      = $this->createListing($seller, ['status' => 'cancelled']);
        $listingImage = $this->makeListingImage($listing);

        $response = $this->actingAs($seller)
            ->delete(route('seller.listings.images.destroy', [$listing, $listingImage]));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_from_image_delete_route(): void
    {
        $seller       = $this->makeUser('seller');
        $listing      = $this->createListing($seller);
        $listingImage = $this->makeListingImage($listing);

        $response = $this->delete(
            route('seller.listings.images.destroy', [$listing, $listingImage])
        );

        $response->assertRedirect();
    }

    // -----------------------------------------------------------------------
    // View tests  (22 – 24)
    // -----------------------------------------------------------------------

    public function test_edit_page_shows_existing_images_for_draft_listing(): void
    {
        $seller       = $this->makeUser('seller');
        $listing      = $this->createListing($seller, ['status' => 'draft']);
        $listingImage = $this->makeListingImage($listing);

        $response = $this->actingAs($seller)
            ->get(route('seller.listings.edit', $listing));

        $response->assertSee(
            route('seller.listings.images.destroy', [$listing, $listingImage])
        );
    }

    public function test_edit_page_shows_upload_form_for_draft_listing(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'draft']);

        $response = $this->actingAs($seller)
            ->get(route('seller.listings.edit', $listing));

        $response->assertSee(route('seller.listings.images.store', $listing));
    }

    public function test_active_listing_edit_page_is_forbidden(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller, ['status' => 'active']);

        $response = $this->actingAs($seller)
            ->get(route('seller.listings.edit', $listing));

        $response->assertForbidden();
    }

    // ── Primary image toggle ──────────────────────────────────────────────────

    public function test_seller_can_set_own_draft_listing_image_as_primary(): void
    {
        $seller  = $this->makeUser('seller');
        $listing = $this->createListing($seller);
        $image   = $this->makeListingImage($listing, [
            'is_primary' => false,
            'sort_order' => 0,
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.images.primary', [$listing, $image]))
            ->assertRedirect(route('seller.listings.edit', $listing))
            ->assertSessionHas('success');
    }

    public function test_setting_primary_unsets_previous_primary_image(): void
    {
        $seller = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $oldPrimary = $this->makeListingImage($listing, [
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $newPrimary = $this->makeListingImage($listing, [
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.images.primary', [$listing, $newPrimary]));

        $this->assertDatabaseHas('listing_images', [
            'id' => $oldPrimary->id,
            'is_primary' => false,
        ]);

        $this->assertDatabaseHas('listing_images', [
            'id' => $newPrimary->id,
            'is_primary' => true,
        ]);
    }

    public function test_seller_cannot_set_primary_image_from_another_sellers_listing(): void
    {
        $seller = $this->makeUser('seller');
        $otherSeller = $this->makeUser('seller');

        $listing = $this->createListing($otherSeller);
        $image = $this->makeListingImage($listing, [
            'is_primary' => false,
            'sort_order' => 0,
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.images.primary', [$listing, $image]))
            ->assertForbidden();
    }

    public function test_seller_cannot_set_primary_on_active_listing(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, [
            'status' => 'active',
        ]);

        $image = $this->makeListingImage($listing, [
            'is_primary' => false,
            'sort_order' => 0,
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.images.primary', [$listing, $image]))
            ->assertForbidden();
    }

    public function test_bidder_cannot_set_primary(): void
    {
        $bidder = $this->makeUser('bidder');
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller);
        $image = $this->makeListingImage($listing, [
            'is_primary' => false,
            'sort_order' => 0,
        ]);

        $this->actingAs($bidder)
            ->patch(route('seller.listings.images.primary', [$listing, $image]))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_primary_route(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller);
        $image = $this->makeListingImage($listing, [
            'is_primary' => false,
            'sort_order' => 0,
        ]);

        $this->patch(route('seller.listings.images.primary', [$listing, $image]))
            ->assertRedirect(route('login'));
    }

    // ── Image sort order ──────────────────────────────────────────────────────

    public function test_seller_can_update_image_sort_order_for_own_draft_listing(): void
    {
        $seller = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $image1 = $this->makeListingImage($listing, [
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $image2 = $this->makeListingImage($listing, [
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.images.sort', $listing), [
                'image_order' => [
                    $image1->id => 1,
                    $image2->id => 0,
                ],
            ])
            ->assertRedirect(route('seller.listings.edit', $listing))
            ->assertSessionHas('success');
    }

    public function test_sort_order_updates_only_images_belonging_to_the_listing(): void
    {
        $seller = $this->makeUser('seller');
        $otherSeller = $this->makeUser('seller');

        $listing = $this->createListing($seller);
        $otherListing = $this->createListing($otherSeller);

        $ownImage = $this->makeListingImage($listing, [
            'sort_order' => 0,
        ]);

        $foreignImage = $this->makeListingImage($otherListing, [
            'sort_order' => 99,
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.images.sort', $listing), [
                'image_order' => [
                    $ownImage->id => 5,
                    $foreignImage->id => 0,
                ],
            ]);

        $this->assertDatabaseHas('listing_images', [
            'id' => $ownImage->id,
            'sort_order' => 5,
        ]);

        $this->assertDatabaseHas('listing_images', [
            'id' => $foreignImage->id,
            'sort_order' => 99,
        ]);
    }

    public function test_seller_cannot_sort_images_on_another_sellers_listing(): void
    {
        $seller = $this->makeUser('seller');
        $otherSeller = $this->makeUser('seller');

        $listing = $this->createListing($otherSeller);
        $image = $this->makeListingImage($listing, [
            'sort_order' => 0,
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.images.sort', $listing), [
                'image_order' => [
                    $image->id => 1,
                ],
            ])
            ->assertForbidden();
    }

    public function test_seller_cannot_sort_images_on_active_listing(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller, [
            'status' => 'active',
        ]);

        $image = $this->makeListingImage($listing, [
            'sort_order' => 0,
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.listings.images.sort', $listing), [
                'image_order' => [
                    $image->id => 1,
                ],
            ])
            ->assertForbidden();
    }

    public function test_bidder_cannot_sort_images(): void
    {
        $bidder = $this->makeUser('bidder');
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller);
        $image = $this->makeListingImage($listing, [
            'sort_order' => 0,
        ]);

        $this->actingAs($bidder)
            ->patch(route('seller.listings.images.sort', $listing), [
                'image_order' => [
                    $image->id => 1,
                ],
            ])
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_sort_route(): void
    {
        $seller = $this->makeUser('seller');

        $listing = $this->createListing($seller);
        $image = $this->makeListingImage($listing, [
            'sort_order' => 0,
        ]);

        $this->patch(route('seller.listings.images.sort', $listing), [
            'image_order' => [
                $image->id => 1,
            ],
        ])
            ->assertRedirect(route('login'));
    }

    // ── Image management view tests ───────────────────────────────────────────

    public function test_edit_page_shows_set_primary_form_for_non_primary_image(): void
    {
        $seller = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $nonPrimary = $this->makeListingImage($listing, [
            'is_primary' => false,
            'sort_order' => 0,
        ]);

        $this->actingAs($seller)
            ->get(route('seller.listings.edit', $listing))
            ->assertSee(route('seller.listings.images.primary', [$listing, $nonPrimary]));
    }

    public function test_edit_page_does_not_show_set_primary_form_for_already_primary_image(): void
    {
        $seller = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $primaryImage = $this->makeListingImage($listing, [
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($seller)
            ->get(route('seller.listings.edit', $listing))
            ->assertDontSee(route('seller.listings.images.primary', [$listing, $primaryImage]));
    }

    public function test_edit_page_shows_sort_order_form_input(): void
    {
        $seller = $this->makeUser('seller');
        $listing = $this->createListing($seller);

        $this->makeListingImage($listing, [
            'sort_order' => 0,
        ]);

        $this->makeListingImage($listing, [
            'sort_order' => 1,
        ]);

        $this->actingAs($seller)
            ->get(route('seller.listings.edit', $listing))
            ->assertSee(route('seller.listings.images.sort', $listing));
    }
}