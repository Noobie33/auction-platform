<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function approve(Request $request, SellerRequest $sellerRequest): RedirectResponse
    {
        abort_unless(
            $sellerRequest->status === 'pending',
            422,
            'Only pending applications can be approved.'
        );

        DB::transaction(function () use ($sellerRequest) {
            $sellerRequest->update([
                'status'      => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            $user = $sellerRequest->user;
            $user->assignRole('seller');
            $user->removeRole('bidder');
        });

        return redirect()
            ->route('admin.seller-applications.index')
            ->with('success', 'Application approved. Seller role assigned.');
    }

 
    public function reject(Request $request, SellerRequest $sellerRequest): RedirectResponse
    {
        abort_unless(
            $sellerRequest->status === 'pending',
            422,
            'Only pending applications can be rejected.'
        );

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($sellerRequest, $validated) {
            $sellerRequest->update([
                'status'      => 'rejected',
                'admin_note'  => $validated['admin_note'],
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.seller-applications.index')
            ->with('success', 'Application rejected.');
    }
}