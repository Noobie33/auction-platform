<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
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
    
    // ── Password reset ───────────────────────────────────────────────────────
    // All four routes are guest-only. The user is not authenticated when
    // they need to reset their password. After a successful reset the
    // controller calls Auth::login() and redirects to /dashboard, at
    // which point the guest middleware no longer applies.

    Route::get('/forgot-password', [PasswordResetController::class, 'request'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetController::class, 'email'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])
        ->name('password.reset');

    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->name('password.update');
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