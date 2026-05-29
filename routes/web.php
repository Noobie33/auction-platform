<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Seller\SellerDashboardController;
use App\Http\Controllers\Bidder\BidderDashboardController;
use App\Http\Controllers\Staff\StaffDashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'check.account.status'])->group(function () {

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

});