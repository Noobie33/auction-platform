<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Show a paginated list of all users.
     *
     * Roles are eager-loaded to avoid N+1 queries on the index page.
     * Ordered by newest registration first so recently joined users
     * appear at the top without requiring a sort control at this stage.
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
}