<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── Dashboard smart redirect ───────────────────────────────────────────────────
// Requires both 'auth' (must be logged in) and 'check.account.status'
// (account must be active). Spatie role check happens inside the controller.

Route::middleware(['auth', 'check.account.status'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ── Temporary role dashboard stubs ────────────────────────────────────────
    // These exist only so DashboardController redirects resolve without error.
    // Replace each one when the real role dashboard controller is built.

    Route::get('/admin/dashboard', function () {
        return 'Admin dashboard — coming soon.';
    })->name('admin.dashboard');

    Route::get('/seller/dashboard', function () {
        return 'Seller dashboard — coming soon.';
    })->name('seller.dashboard');

    Route::get('/bidder/dashboard', function () {
        return 'Bidder dashboard — coming soon.';
    })->name('bidder.dashboard');

    Route::get('/staff/dashboard', function () {
        return 'Staff dashboard — coming soon.';
    })->name('staff.dashboard');

});