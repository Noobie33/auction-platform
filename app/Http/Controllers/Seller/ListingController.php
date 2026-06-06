<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\View\View;
use App\Http\Requests\Seller\StoreListingRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Seller\UpdateListingRequest;

class ListingController extends Controller
{
    /**
     * Display the authenticated seller's own listings.
     *
     * scopeForSeller() scopes every query to the authenticated user's seller_id
     * so no seller can ever see another seller's listings on this route.
     * SoftDeletes global scope excludes soft-deleted listings automatically.
     */
    public function index(): View
    {
        $listings = Listing::forSeller(auth()->id())
            ->with('category')
            ->latest()
            ->get();

        return view('seller.listings.index', compact('listings'));
    }

    /**
     * Show the create listing form.
     *
     * Category dropdown: active roots with their active children as optgroups.
     * Parents with no active children appear as directly selectable options
     * (e.g. 'Other'). Inactive categories are excluded from the dropdown
     * and blocked at the validation layer in StoreListingRequest.
     */
    public function create(): View
    {
        $categories = Category::active()
            ->roots()
            ->with(['children' => fn ($q) => $q->active()->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('seller.listings.create', compact('categories'));
    }

    /**
     * Validate and persist a new listing as a draft.
     *
     * Fields the seller must never control:
     *   status     → always 'draft' on creation
     *   slug       → always auto-generated from title
     *   seller_id  → always auth()->id()
     *   is_featured, view_count, winner_id, current_bid → server-only
     *
     * min_bid_increment defaults to 10.00 (matches DB default) when omitted.
     * auto_extend defaults to true (matches DB default) when checkbox omitted.
     */
    public function store(StoreListingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Listing::create([
            'seller_id'         => auth()->id(),
            'category_id'       => $data['category_id'],
            'title'             => $data['title'],
            'slug'              => Listing::generateSlug($data['title']),
            'description'       => $data['description'],
            'condition'         => $data['condition'],
            'location'          => $data['location'] ?? null,
            'starting_price'    => $data['starting_price'],
            'reserve_price'     => $data['reserve_price'] ?? null,
            'min_bid_increment' => $data['min_bid_increment'] ?? 10.00,
            'buy_now_price'     => $data['buy_now_price'] ?? null,
            'starts_at'         => $data['starts_at'] ?? null,
            'ends_at'           => $data['ends_at'] ?? null,
            'auto_extend'       => (bool) ($data['auto_extend'] ?? true),
            'status'            => 'draft',
        ]);

        return redirect()->route('seller.listings.index');
    }

    /**
     * Show the edit form for a draft listing.
     *
     * Guard order matters:
     *   1. isOwnedBy() — prevents seller A seeing seller B's listing.
     *   2. isDraft()   — prevents editing a published or cancelled listing.
     *
     * Both guards run before any data is passed to the view.
     */
    public function edit(Listing $listing): View
    {
        abort_if(! $listing->isOwnedBy(auth()->user()), 403);
        abort_if(! $listing->isDraft(), 403);

        $categories = Category::active()
            ->roots()
            ->with(['children' => fn ($q) => $q->active()->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('seller.listings.edit', compact('listing', 'categories'));
    }

    /**
     * Validate and persist changes to a draft listing.
     *
     * The same two guards from edit() are repeated here. Without them a
     * seller could bypass the UI and PATCH directly to the URL.
     *
     * Fields the seller must never control on update:
     *   seller_id, status, is_featured, view_count, current_bid, winner_id
     *
     * Slug is regenerated from the new title. Passing $listing->id to
     * generateSlug() excludes the current row from the uniqueness check so
     * updating with an unchanged title preserves the existing slug without
     * incorrectly appending a -2 suffix.
     *
     * min_bid_increment falls back to 10.00 (DB default) when omitted.
     * auto_extend falls back to current model value when omitted (test PATCH path).
     */
    public function update(UpdateListingRequest $request, Listing $listing): RedirectResponse
    {
        abort_if(! $listing->isOwnedBy(auth()->user()), 403);
        abort_if(! $listing->isDraft(), 403);

        $data = $request->validated();

        $listing->update([
            'category_id'       => $data['category_id'],
            'title'             => $data['title'],
            'slug'              => Listing::generateSlug($data['title'], $listing->id),
            'description'       => $data['description'],
            'condition'         => $data['condition'],
            'location'          => $data['location'] ?? null,
            'starting_price'    => $data['starting_price'],
            'reserve_price'     => $data['reserve_price'] ?? null,
            'min_bid_increment' => $data['min_bid_increment'] ?? 10.00,
            'buy_now_price'     => $data['buy_now_price'] ?? null,
            'starts_at'         => $data['starts_at'] ?? null,
            'ends_at'           => $data['ends_at'] ?? null,
            'auto_extend'       => (bool) ($data['auto_extend'] ?? $listing->auto_extend),
        ]);

        return redirect()->route('seller.listings.index');
    }

    /**
     * Publish a draft listing, making it live and accepting bids.
     *
     * Guard order:
     *   1. isOwnedBy() — cross-seller protection.
     *   2. isDraft()   — published/cancelled/ended listings cannot be republished.
     *
     * Business rules enforced before the status transition:
     *   - ends_at must be present.
     *   - ends_at must be in the future AND after the effective start time.
     *   - If starts_at is null, it is set to now() at the moment of publishing.
     *
     * $effectiveStart uses starts_at when it is set (future-dated auctions),
     * or falls back to now() for auctions that should begin immediately.
     * This means a past ends_at always fails the lte() check when starts_at
     * is null, satisfying the "past ends_at is blocked" requirement.
     */
    public function publish(Listing $listing): RedirectResponse
    {
        abort_if(! $listing->isOwnedBy(auth()->user()), 403);
        abort_if(! $listing->isDraft(), 403);

        if (
            blank($listing->category_id) ||
            blank($listing->title) ||
            blank($listing->description) ||
            blank($listing->condition) ||
            blank($listing->starting_price)
        ) {
            return redirect()
                ->route('seller.listings.index')
                ->with('error', "Complete all required listing details before publishing '{$listing->title}'.");
        }

        if (is_null($listing->ends_at)) {
            return redirect()
                ->route('seller.listings.index')
                ->with('error', "Set an auction end date before publishing '{$listing->title}'.");
        }

        $effectiveStart = $listing->starts_at ?? now();

        if ($listing->ends_at->isPast() || $listing->ends_at->lte($effectiveStart)) {
            return redirect()
                ->route('seller.listings.index')
                ->with('error', 'Auction end date must be in the future and after the start date.');
        }

        $listing->update([
            'status'    => 'active',
            'starts_at' => $listing->starts_at ?? now(),
        ]);

        return redirect()
            ->route('seller.listings.index')
            ->with('success', "'{$listing->title}' is now live and accepting bids.");
    }

    /**
     * Cancel an active listing, ending it early.
     *
     * Guard order:
     *   1. isOwnedBy() — cross-seller protection.
     *   2. isActive()  — only active listings can be cancelled. Draft listings
     *      remain editable and are handled differently (deletion in a later unit).
     *      Cancelled, ended, and failed listings are terminal states.
     *
     * No validation form is needed — this is a one-click state transition
     * guarded at the controller layer, not the form layer.
     */
    public function cancel(Listing $listing): RedirectResponse
    {
        abort_if(! $listing->isOwnedBy(auth()->user()), 403);
        abort_if(! $listing->isActive(), 403);

        $listing->update(['status' => 'cancelled']);

        return redirect()
            ->route('seller.listings.index')
            ->with('success', "'{$listing->title}' has been cancelled.");
    }

    public function destroy(Listing $listing): RedirectResponse
    {
        abort_if(! $listing->isOwnedBy(auth()->user()), 403);
        abort_if(! $listing->isDraft(), 403);

        $listing->delete();

        return redirect()
            ->route('seller.listings.index')
            ->with('success', "'{$listing->title}' draft has been deleted.");
    }
}