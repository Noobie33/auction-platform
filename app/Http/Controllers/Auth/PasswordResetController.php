<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function request(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send the password reset link to the given email address.
     *
     * Password::sendResetLink() looks up the user by email, creates a
     * signed token in password_reset_tokens, and dispatches the
     * ResetPassword notification. The broker handles all of this.
     *
     * We do not reveal whether the email exists in our database beyond
     * what Laravel's default language lines already say. No extra
     * enumeration protection is added at this stage.
     */
    public function email(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show the reset password form.
     *
     * The signed email verification link appends ?email= to the URL.
     * We pass it to the view so the form can pre-fill the email field
     * and include it as a hidden input alongside the token.
     */
    public function reset(string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request()->query('email', ''),
        ]);
    }

    /**
     * Handle the new password submission.
     *
     * Password::reset() validates the token, finds the user by email,
     * calls the closure, then deletes the used token automatically.
     *
     * Password field note: the User model declares 'password' with the
     * 'hashed' cast, which bcrypts the value automatically on assignment.
     * We must pass the plain-text password here — do NOT wrap it in
     * Hash::make() or the password will be double-hashed and logins
     * will fail silently.
     *
     * After a successful reset the user is logged in immediately.
     * This is standard Laravel behavior: the signed token proves
     * ownership of the email address, so requiring a separate login
     * step would be redundant friction. The user is then subject to
     * the same auth + check.account.status + verified middleware stack
     * as any other authenticated user.
     */
    public function update(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // Update password — 'hashed' cast handles bcrypt automatically.
                // Rotate the remember token so any existing "remember me"
                // cookies from before the reset are invalidated immediately.
                $user->forceFill([
                    'password'       => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                Auth::login($user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('dashboard')->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }
}