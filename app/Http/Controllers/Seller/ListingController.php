<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\View\View;
use App\Http\Requests\Seller\StoreListingRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;

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
}