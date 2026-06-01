<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Seller\SellerApplicationController;
use App\Http\Controllers\Seller\SellerDashboardController;
use App\Http\Controllers\Bidder\BidderDashboardController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Profile\ProfileController;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'check.account.status', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/admin/dashboard', AdminDashboardController::class)
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/seller/dashboard', SellerDashboardController::class)
        ->middleware('role:seller')
        ->name('seller.dashboard');

    Route::get('/bidder/dashboard', BidderDashboardController::class)
        ->middleware('role:bidder')
        ->name('bidder.dashboard');

    Route::get('/staff/dashboard', StaffDashboardController::class)
        ->middleware('role:staff')
        ->name('staff.dashboard');

    // ── Profile ───────────────────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ── Seller Application ────────────────────────────────────────────────────
    // All three routes require role:bidder in addition to the group middleware.
    // Sellers, admins, and staff cannot access these routes — they have no
    // reason to submit an application and would receive a 403.

    Route::get('/seller/apply', [SellerApplicationController::class, 'create'])
        ->middleware('role:bidder')
        ->name('seller.apply');

    Route::post('/seller/apply', [SellerApplicationController::class, 'store'])
        ->middleware('role:bidder')
        ->name('seller.apply.store');

    Route::get('/seller/application/status', [SellerApplicationController::class, 'status'])
        ->middleware('role:bidder')
        ->name('seller.application.status');

});