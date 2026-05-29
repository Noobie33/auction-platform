<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a login attempt.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Step 1: Validate credentials against the database.
        // Auth::attempt() verifies the hashed password automatically.
        // If it returns false, the credentials do not match any record.
        if (! Auth::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors(['email' => 'The credentials you entered do not match our records.'])
                ->onlyInput('email');
        }

        // Step 2: Credentials are valid — now check account status.
        // This must happen BEFORE session regeneration to avoid
        // completing a login for a suspended or banned account.
        $user = Auth::user();

        if ($user->status !== 'active') {
            // Immediately undo the successful Auth::attempt().
            // All three calls are required for a clean, secure logout:
            // - logout()             removes the authenticated user from the session
            // - invalidate()         destroys the entire session data
            // - regenerateToken()    issues a fresh CSRF token for the new session
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Your account has been ' . $user->status . '. Please contact support.',
                ]);
        }

        // Step 3: Account is active.
        // Regenerate the session ID to prevent session fixation attacks.
        // This must happen after the status check and before any redirect.
        $request->session()->regenerate();

        // Step 4: Record the login timestamp.
        // Uses update() directly — no event firing, no unnecessary overhead.
        $user->update(['last_login_at' => now()]);

        // Step 5: Redirect to the intended URL if the user was redirected
        // to login from a protected route, otherwise fall back to /dashboard.
        // Role-based dashboard routing will be added later in DashboardController.
        return redirect()->intended('/dashboard');
    }
}