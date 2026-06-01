<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerApplicationManagementController extends Controller
{
    /**
     * Show a paginated, filterable list of seller applications.
     *
     * Defaults to pending so the most actionable applications are
     * always visible without requiring a filter selection.
     *
     * withQueryString() appends the active filter to all pagination
     * links so navigating pages does not reset the filter.
     */
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'pending');

        if (! in_array($filter, ['pending', 'approved', 'rejected', 'all'], true)) {
            $filter = 'pending';
        }

        $query = SellerRequest::with(['user', 'reviewer'])
            ->latest();

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        $applications = $query->paginate(20)->withQueryString();

        return view('admin.seller-applications.index', [
            'applications' => $applications,
            'filter'       => $filter,
        ]);
    }
}