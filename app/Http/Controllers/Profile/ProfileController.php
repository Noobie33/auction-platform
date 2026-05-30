<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Handle the profile update.
     *
     * $request->validated() contains only the fields declared in
     * UpdateProfileRequest::rules(). Email, password, status, and
     * avatar_path are absent from those rules and therefore can never
     * be modified through this method, even if submitted in the request.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}