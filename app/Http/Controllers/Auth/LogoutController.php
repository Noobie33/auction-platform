<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Log the user out and clear all session state.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Step 1: Remove the authenticated user from the session.
        Auth::logout();

        // Step 2: Destroy all session data.
        // This clears flash messages, intended URLs, and any other
        // session values the current request accumulated.
        $request->session()->invalidate();

        // Step 3: Issue a fresh CSRF token for the new anonymous session.
        // Without this, the old token remains valid for a window of time,
        // which is a minor but real security exposure.
        $request->session()->regenerateToken();

        // Step 4: Send the user back to the login page.
        // Requires a route named 'login' — which will be defined when
        // routes are built in the next step. If that name changes,
        // update it here as well.
        return redirect()->route('login');
    }
}