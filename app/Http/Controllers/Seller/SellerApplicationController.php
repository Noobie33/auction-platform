<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerApplicationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SellerApplicationController extends Controller
{
    /**
     * Show the seller application form.
     *
     * If the user already has any application on record — pending, approved,
     * or rejected — redirect to the status page instead of showing the form.
     * One application per user is enforced here and again in store() to
     * guard against race conditions and direct POST requests.
     */
    public function create(): View|RedirectResponse
    {
        $application = auth()->user()->sellerRequests()->latest()->first();

        if ($application) {
            return redirect()->route('seller.application.status');
        }

        return view('seller.apply');
    }

    /**
     * Handle the seller application submission.
     *
     * The duplicate check is repeated here intentionally. A user who
     * opens two tabs or crafts a direct POST request must not be able
     * to insert a second row.
     *
     * user_id is set automatically by the HasMany relationship when
     * creating through sellerRequests(). status defaults to 'pending'
     * at the DB level but is passed explicitly for clarity.
     */
    public function store(SellerApplicationRequest $request): RedirectResponse
    {
        $existing = $request->user()->sellerRequests()->latest()->first();

        if ($existing) {
            return redirect()->route('seller.application.status');
        }

        $request->user()->sellerRequests()->create([
            'motivation' => $request->validated()['motivation'],
            'status'     => 'pending',
        ]);

        return redirect()->route('seller.application.status')
            ->with('status', 'Your application has been submitted. We will review it shortly.');
    }

    /**
     * Show the application status page.
     *
     * If the user has no application yet, redirect to the form.
     * Passing the latest application covers the edge case where an admin
     * might delete and reopen an application in a future phase.
     */
    public function status(): View|RedirectResponse
    {
        $application = auth()->user()->sellerRequests()->latest()->first();

        if (! $application) {
            return redirect()->route('seller.apply');
        }

        return view('seller.application-status', [
            'application' => $application,
        ]);
    }
}