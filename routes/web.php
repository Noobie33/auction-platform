<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SellerApplicationManagementController;
use App\Http\Controllers\Seller\SellerApplicationController;
use App\Http\Controllers\Seller\SellerDashboardController;
use App\Http\Controllers\Bidder\BidderDashboardController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Seller\ListingController;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'check.account.status', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Admin ─────────────────────────────────────────────────────────────────
    Route::get('/admin/dashboard', AdminDashboardController::class)
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/admin/users', [UserManagementController::class, 'index'])
        ->middleware('role:admin')
        ->name('admin.users.index');

    Route::patch('/admin/users/{user}/status', [UserManagementController::class, 'updateStatus'])
        ->middleware('role:admin')
        ->name('admin.users.status.update');

     Route::get('/admin/seller-applications', [SellerApplicationManagementController::class, 'index'])
        ->middleware('role:admin')
        ->name('admin.seller-applications.index');

    Route::post('admin/seller-applications/{sellerRequest}/approve', [SellerApplicationManagementController::class, 'approve'])
        ->name('admin.seller-applications.approve')
        ->middleware('role:admin');

    Route::post('admin/seller-applications/{sellerRequest}/reject', [SellerApplicationManagementController::class, 'reject'])
        ->name('admin.seller-applications.reject')
        ->middleware('role:admin');

    Route::get('/admin/categories', [CategoryController::class, 'index'])
        ->middleware('role:admin')
        ->name('admin.categories.index');

    Route::get('/admin/categories/create', [CategoryController::class, 'create'])
        ->middleware('role:admin')
        ->name('admin.categories.create');

    Route::post('/admin/categories', [CategoryController::class, 'store'])
        ->middleware('role:admin')
        ->name('admin.categories.store');

    Route::get('/admin/categories/{category}/edit', [CategoryController::class, 'edit'])
        ->middleware('role:admin')
        ->name('admin.categories.edit');

    Route::patch('/admin/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('role:admin')
        ->name('admin.categories.update');

    Route::patch('/admin/categories/{category}/status', [CategoryController::class, 'updateStatus'])
        ->middleware('role:admin')
        ->name('admin.categories.status.update');

    Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('admin.categories.destroy');

    // ── Seller ────────────────────────────────────────────────────────────────
    Route::get('/seller/dashboard', SellerDashboardController::class)
        ->middleware('role:seller')
        ->name('seller.dashboard');
    
    Route::get('/seller/listings', [ListingController::class, 'index'])
        ->middleware('role:seller')
        ->name('seller.listings.index');

    Route::get('/seller/listings/create', [ListingController::class, 'create'])
        ->middleware('role:seller')
        ->name('seller.listings.create');

    Route::post('/seller/listings', [ListingController::class, 'store'])
        ->middleware('role:seller')
        ->name('seller.listings.store');

    Route::get('/seller/listings/{listing}/edit', [ListingController::class, 'edit'])
        ->middleware('role:seller')
        ->name('seller.listings.edit');

    Route::patch('/seller/listings/{listing}', [ListingController::class, 'update'])
        ->middleware('role:seller')
        ->name('seller.listings.update');

    Route::patch('/seller/listings/{listing}/publish', [ListingController::class, 'publish'])
        ->middleware('role:seller')
        ->name('seller.listings.publish');

    Route::patch('/seller/listings/{listing}/cancel', [ListingController::class, 'cancel'])
        ->middleware('role:seller')
        ->name('seller.listings.cancel');

     // ── Bidder ────────────────────────────────────────────────────────────────
    Route::get('/bidder/dashboard', BidderDashboardController::class)
        ->middleware('role:bidder')
        ->name('bidder.dashboard');

    // ── Staff ─────────────────────────────────────────────────────────────────
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