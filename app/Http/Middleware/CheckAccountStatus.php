<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * Runs on every authenticated route. If the user is not logged in,
     * the request passes through untouched — the 'auth' middleware
     * handles unauthenticated users separately.
     *
     * If the authenticated user's status is not 'active', they are
     * logged out immediately and redirected to the login page with
     * a clear error message, regardless of how they arrived here.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Step 1: Only act on authenticated users.
        // Unauthenticated requests are not this middleware's concern.
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Step 2: Active accounts proceed normally.
        if ($user->status === 'active') {
            return $next($request);
        }

        // Step 3: Account is suspended or banned.
        // Undo the session completely — same three-call pattern
        // used in LoginController and LogoutController for consistency.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Step 4: Redirect with a message that names the status clearly
        // so the user understands why they were forced out.
        // Requires a route named 'login' — to be defined in the routes step.
        return redirect()->route('login')
            ->withErrors([
                'email' => 'Your account has been ' . $user->status . '. Please contact support.',
            ]);
    }
}