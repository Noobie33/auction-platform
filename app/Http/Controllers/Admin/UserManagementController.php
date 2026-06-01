<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Show a paginated list of all users.
     *
     * Roles are eager-loaded to avoid N+1 queries on the index page.
     * Ordered by newest registration first.
     */
    public function index(): View
    {
        $users = User::with('roles')
            ->latest()
            ->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Update the account status of a given user.
     *
     * Two guards are enforced at the controller level because they
     * require knowledge of both the acting admin and the target user,
     * which is not available at the middleware layer:
     *
     * 1. An admin cannot alter their own status. This prevents
     *    accidental self-lockout.
     *
     * 2. An admin cannot alter another admin's status. This prevents
     *    privilege abuse between admin accounts. If multi-tier admin
     *    management is needed in future, it belongs in a separate
     *    super-admin role, not here.
     */
    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            abort(403, 'You cannot change your own account status.');
        }

        if ($user->hasRole('admin')) {
            abort(403, 'You cannot change the status of another administrator.');
        }

        $validated = $request->validated();

        $user->update(['status' => $validated['status']]);

        return redirect()->route('admin.users.index')
            ->with('status', "Status for {$user->name} has been updated to {$validated['status']}.");
    }
}