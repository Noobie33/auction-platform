<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Show the email verification notice.
     *
     * Shown to authenticated users who have not yet verified their email.
     * The 'verified' middleware redirects already-verified users away
     * from this page, so we only ever render it for unverified users.
     */
    public function notice(): View
    {
        return view('auth.verify-email');
    }

    /**
     * Handle the email verification link click.
     *
     * EmailVerificationRequest is a Laravel built-in FormRequest that
     * automatically validates the signed URL, checks the hash against
     * the authenticated user's email address, and handles the timing.
     * No manual signature checking is needed here.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        // Mark the user's email as verified and fire the Verified event.
        $request->fulfill();

        return redirect()->route('dashboard')
            ->with('status', 'Your email address has been verified.');
    }

    /**
     * Resend the verification notification.
     *
     * Rate-limited by the 'throttle:6,1' middleware on the route.
     * Only reachable by authenticated, unverified users.
     */
    public function resend(Request $request): RedirectResponse
    {
        // Guard: do nothing if already verified.
        // This handles the edge case where someone hits resend after
        // verifying in another tab.
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A fresh verification link has been sent to your email address.');
    }
}