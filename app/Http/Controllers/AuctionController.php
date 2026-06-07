<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\View\View;

class AuctionController extends Controller
{
    /**
     * Browse all active auction listings, ordered by soonest-ending first.
     *
     * Public route: accessible to guests and all authenticated users.
     * Search and category filtering are intentionally deferred to Phase 8 Unit 2.
     */
    public function index(): View
    {
        $listings = Listing::where('status', 'active')
            ->with([
                'category',
                'images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            ])
            ->orderBy('ends_at', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(12)
            ->withQueryString();

        return view('auctions.index', compact('listings'));
    }
}