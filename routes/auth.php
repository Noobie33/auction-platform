<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

// ── Guest-only routes ─────────────────────────────────────────────────────────
// 'guest' middleware redirects already-authenticated users away from
// login and register pages. No reason to show a login form to someone
// who is already logged in.

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'store'])
        ->name('register.store');
});

// ── Authenticated-only routes ─────────────────────────────────────────────────
// 'auth' middleware ensures only logged-in users can POST to logout.
// Without this, an unauthenticated request to /logout would cause
// Auth::logout() to run on a guest session, which is harmless but sloppy.

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LogoutController::class, 'destroy'])
        ->name('logout');

    // Email verification notice — shown to unverified users.
    // The 'verified' middleware on other routes redirects here automatically.
    Route::get('/email/verify', [VerificationController::class, 'notice'])
        ->name('verification.notice');

    // Email verification handler — processes the signed link from the email.
    // 'signed' ensures the URL has not been tampered with.
    // 'throttle:6,1' prevents brute-force link generation.
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Resend verification email.
    // 'throttle:6,1' prevents abuse — max 6 resend requests per minute.
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});