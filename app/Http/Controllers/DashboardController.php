<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect the authenticated user to their role-specific dashboard.
     *
     * This controller has one job: read the user's Spatie role and send
     * them to the correct dashboard. It owns no view and holds no logic
     * beyond the redirect decision.
     */
    public function index(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('seller')) {
            return redirect()->route('seller.dashboard');
        }

        if ($user->hasRole('bidder')) {
            return redirect()->route('bidder.dashboard');
        }

        if ($user->hasRole('staff')) {
            return redirect()->route('staff.dashboard');
        }

        // The authenticated user exists but has no recognised role.
        // This should never happen in normal operation — it means a user
        // was created without assignRole() being called, or their role
        // was manually deleted from the database.
        // Force a clean logout rather than leaving them in a broken state.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors([
                'email' => 'Your account has no role assigned. Please contact support.',
            ]);
    }
}