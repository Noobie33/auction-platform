<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration attempt.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Step 1: Create the user using only the explicitly named validated fields.
        //
        // safe()->only([...]) is deliberately more explicit than validated() alone.
        // It means: even if RegisterRequest::rules() is accidentally expanded in the
        // future to include other fields, this create() call will never pick them up
        // unless you consciously add them here.
        //
        // The password is passed as plain text. The User model's 'hashed' cast
        // handles bcrypt hashing automatically before the INSERT runs.
        // Do not call Hash::make() here — that responsibility belongs to the model.
        //
        // status is set explicitly to 'active' for readability. The DB default
        // is also 'active', but being explicit here means any developer reading
        // this code understands the intended state without checking the migration.
        //
        // role, avatar_path, bio, last_login_at, and email_verified_at are
        // never touched here. They cannot be mass-assigned from this controller
        // because they are absent from safe()->only([...]).
        $user = User::create([
            ...$request->safe()->only([
                'name',
                'username',
                'email',
                'password',
                'phone',
            ]),
            'status' => 'active',
        ]);

        // Step 2: Assign the bidder role via Spatie.
        // Every public registration becomes a bidder — no exceptions.
        // admin, seller, and staff roles are assigned only through
        // controlled internal flows, never through public registration.
        $user->assignRole('bidder');

        // Step 3: Log the new user in immediately.
        // Auth::login() writes the user's ID to the session.
        Auth::login($user);

        // Step 4: Regenerate the session ID to prevent session fixation.
        // Required any time a privilege change occurs — registration counts.
        $request->session()->regenerate();

        // Step 5: Record the login timestamp for consistency.
        // LoginController does this after every successful login.
        // Registration also logs the user in, so the same rule applies.
        $user->update(['last_login_at' => now()]);

        // Send the email verification notification.
        // The User model implements MustVerifyEmail, which provides this method.
        // Laravel sends a signed verification URL to the user's email address.
        // The user must click the link before they can access verified routes.
        $user->sendEmailVerificationNotification();

        // Step 6: Redirect to dashboard.
        // No redirect()->intended() here — a brand new user has no prior
        // intended URL stored in their session, so intended() would always
        // fall back to the default anyway. Plain redirect is more honest.
        // Role-based dashboard routing will be handled in DashboardController.
        return redirect('/dashboard');
    }
}