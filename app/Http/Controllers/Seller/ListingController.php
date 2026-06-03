<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\View\View;

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
}