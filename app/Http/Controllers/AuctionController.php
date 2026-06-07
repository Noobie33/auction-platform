<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuctionController extends Controller
{
    /**
     * Browse active auction listings with optional keyword search
     * and active category filtering via GET query parameters.
     *
     * Public route: accessible to guests and all authenticated users.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $categoryId = (int) $request->input('category', 0);

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        $hasValidCategoryFilter = $categoryId > 0
            && $categories->contains('id', $categoryId);

        $query = Listing::where('status', 'active')
            ->with([
                'category',
                'images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            ])
            ->orderBy('ends_at', 'asc')
            ->orderBy('id', 'asc');

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($categoryId > 0) {
            if ($hasValidCategoryFilter) {
                $query->where('category_id', $categoryId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $listings = $query->paginate(12)->withQueryString();

        return view('auctions.index', compact(
            'listings',
            'categories',
            'search',
            'categoryId'
        ));
    }
}