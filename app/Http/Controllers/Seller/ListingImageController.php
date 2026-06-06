<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreListingImageRequest;
use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ListingImageController extends Controller
{
    public function store(Listing $listing, StoreListingImageRequest $request): RedirectResponse
    {
        abort_if(! $listing->isOwnedBy(auth()->user()), 403);
        abort_if(! $listing->isDraft(), 403);

        // Capture counts BEFORE creating the new record so is_primary
        // and sort_order are based on the pre-upload state.
        $isPrimary = $listing->images()->count() === 0;
        $sortOrder = (int) ($listing->images()->max('sort_order') ?? -1) + 1;

        $path = $request->file('image')->store("listings/{$listing->id}", 'public');

        $listing->images()->create([
            'image_path' => $path,
            'alt_text'   => $request->input('alt_text'),
            'is_primary' => $isPrimary,
            'sort_order' => $sortOrder,
        ]);

        return redirect()->route('seller.listings.edit', $listing)
            ->with('success', 'Image uploaded.');
    }

    public function destroy(Listing $listing, ListingImage $listingImage): RedirectResponse
    {
        abort_if(! $listing->isOwnedBy(auth()->user()), 403);
        abort_if(! $listing->isDraft(), 403);

        // Guard against cross-listing image manipulation.
        abort_if((int) $listingImage->listing_id !== (int) $listing->id, 404);

        // Storage::delete is silent if the file is already missing; that is
        // acceptable — the DB record is the source of truth.
        Storage::disk('public')->delete($listingImage->image_path);
        $listingImage->delete();

        return redirect()->route('seller.listings.edit', $listing)
            ->with('success', 'Image deleted.');
    }
}