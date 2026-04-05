<?php

use App\Http\Controllers\Admin\AdminBadgeController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrganizerController;
use App\Http\Controllers\Admin\AdminPlaceController;
use App\Http\Controllers\Admin\AdminRewardController;
use App\Http\Controllers\Organizer\OrganizerBookingController;
use App\Http\Controllers\Organizer\OrganizerDashboardController;
use App\Http\Controllers\Organizer\OrganizerEventController;
use App\Http\Controllers\Organizer\OrganizerEventPhotoController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

// Firebase Google sign-in for web organizers
Route::post('/auth/firebase-google', [\App\Http\Controllers\Auth\FirebaseWebAuthController::class, 'handleGoogleSignIn'])
    ->name('auth.firebase-google');

// Admin dashboard routes (Laravel session auth)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('/places', AdminPlaceController::class)->names('admin.places');
    Route::resource('/badges', AdminBadgeController::class)->names('admin.badges');
    Route::post('/organizers/{user}/verify', [AdminOrganizerController::class, 'verify'])->name('admin.organizers.verify');
    Route::get('/organizers', [AdminOrganizerController::class, 'index'])->name('admin.organizers.index');

    // Rewards
    Route::resource('/rewards', AdminRewardController::class)->names('admin.rewards');
    Route::get('/rewards-redemptions', [AdminRewardController::class, 'redemptions'])->name('admin.rewards.redemptions');
    Route::post('/rewards-redemptions/{redemption}/approve', [AdminRewardController::class, 'approveRedemption'])->name('admin.rewards.redemptions.approve');
    Route::post('/rewards-redemptions/{redemption}/reject', [AdminRewardController::class, 'rejectRedemption'])->name('admin.rewards.redemptions.reject');
    Route::post('/rewards-redemptions/{redemption}/claim', [AdminRewardController::class, 'claimRedemption'])->name('admin.rewards.redemptions.claim');
});

// Organizer dashboard routes (Laravel session auth)
Route::middleware(['auth', 'role:organizer'])->prefix('organizer')->group(function () {
    Route::get('/dashboard', [OrganizerDashboardController::class, 'index'])->name('organizer.dashboard');
    Route::resource('/events', OrganizerEventController::class)->names('organizer.events');
    Route::post('/events/{event}/publish', [OrganizerEventController::class, 'publish'])->name('organizer.events.publish');
    Route::post('/events/{event}/complete', [OrganizerEventController::class, 'complete'])->name('organizer.events.complete');
    Route::get('/events/{event}/bookings', [OrganizerBookingController::class, 'index'])->name('organizer.bookings.index');
    Route::post('/bookings/{booking}/approve', [OrganizerBookingController::class, 'approve'])->name('organizer.bookings.approve');
    Route::post('/bookings/{booking}/reject', [OrganizerBookingController::class, 'reject'])->name('organizer.bookings.reject');
    Route::post('/events/{event}/photos', [OrganizerEventPhotoController::class, 'store'])->name('organizer.events.photos.store');
});
