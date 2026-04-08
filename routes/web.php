<?php

use App\Http\Controllers\Admin\AdminBadgeController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrganizerController;
use App\Http\Controllers\Admin\AdminPlaceController;
use App\Http\Controllers\Admin\AdminRewardController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminXpController;
use App\Http\Controllers\Organizer\OrganizerBookingController;
use App\Http\Controllers\Organizer\OrganizerDashboardController;
use App\Http\Controllers\Organizer\OrganizerEventController;
use App\Http\Controllers\Organizer\OrganizerEventPhotoController;
use App\Http\Controllers\Organizer\OrganizerProfileController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/leaderboard', function () {
    return view('leaderboard');
})->name('leaderboard');

Route::get('/rewards', function () {
    return view('rewards');
})->name('rewards.page');

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

// Google OAuth for organizer login/register
Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// Hidden admin login (only you know this URL)
Route::get('/xadmin', [\App\Http\Controllers\Admin\AdminLoginController::class, 'show'])->name('admin.login');
Route::post('/xadmin', [\App\Http\Controllers\Admin\AdminLoginController::class, 'login'])->name('admin.login.submit');

// Admin dashboard routes (Laravel session auth)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('/places', AdminPlaceController::class)->names('admin.places');
    Route::post('/places/{place}/activate', [AdminPlaceController::class, 'activate'])->name('admin.places.activate');
    Route::resource('/badges', AdminBadgeController::class)->names('admin.badges');
    Route::post('/organizers/{user}/verify', [AdminOrganizerController::class, 'verify'])->name('admin.organizers.verify');
    Route::get('/organizers', [AdminOrganizerController::class, 'index'])->name('admin.organizers.index');

    // Rewards
    Route::resource('/rewards', AdminRewardController::class)->names('admin.rewards');
    Route::get('/rewards-redemptions', [AdminRewardController::class, 'redemptions'])->name('admin.rewards.redemptions');
    Route::post('/rewards-redemptions/{redemption}/approve', [AdminRewardController::class, 'approveRedemption'])->name('admin.rewards.redemptions.approve');
    Route::post('/rewards-redemptions/{redemption}/reject', [AdminRewardController::class, 'rejectRedemption'])->name('admin.rewards.redemptions.reject');
    Route::post('/rewards-redemptions/{redemption}/claim', [AdminRewardController::class, 'claimRedemption'])->name('admin.rewards.redemptions.claim');

    // Events (review/approve)
    Route::get('/events', [AdminEventController::class, 'index'])->name('admin.events.index');
    Route::get('/events/{event}', [AdminEventController::class, 'show'])->name('admin.events.show');
    Route::post('/events/{event}/approve', [AdminEventController::class, 'approve'])->name('admin.events.approve');
    Route::post('/events/{event}/reject', [AdminEventController::class, 'reject'])->name('admin.events.reject');

    // XP Management
    Route::get('/xp', [AdminXpController::class, 'index'])->name('admin.xp.index');
    Route::post('/xp/grant', [AdminXpController::class, 'grant'])->name('admin.xp.grant');
});

// Organizer dashboard routes (Laravel session auth)
Route::middleware(['auth', 'role:organizer'])->prefix('organizer')->group(function () {
    // Onboarding (accessible even before completing)
    Route::get('/onboarding', [\App\Http\Controllers\Organizer\OrganizerOnboardingController::class, 'show'])->name('organizer.onboarding');
    Route::post('/onboarding', [\App\Http\Controllers\Organizer\OrganizerOnboardingController::class, 'store'])->name('organizer.onboarding.store');

    Route::get('/dashboard', [OrganizerDashboardController::class, 'index'])->name('organizer.dashboard');
    Route::resource('/events', OrganizerEventController::class)->names('organizer.events');
    Route::post('/events/{event}/publish', [OrganizerEventController::class, 'publish'])->name('organizer.events.publish');
    Route::post('/events/{event}/complete', [OrganizerEventController::class, 'complete'])->name('organizer.events.complete');
    Route::post('/events/{event}/cancel', [OrganizerEventController::class, 'cancel'])->name('organizer.events.cancel');
    Route::get('/events/{event}/bookings', [OrganizerBookingController::class, 'index'])->name('organizer.bookings.index');
    Route::post('/bookings/{booking}/approve', [OrganizerBookingController::class, 'approve'])->name('organizer.bookings.approve');
    Route::post('/bookings/{booking}/reject', [OrganizerBookingController::class, 'reject'])->name('organizer.bookings.reject');
    Route::post('/events/{event}/photos', [OrganizerEventPhotoController::class, 'store'])->name('organizer.events.photos.store');

    // Profile
    Route::get('/profile', [OrganizerProfileController::class, 'edit'])->name('organizer.profile');
    Route::put('/profile', [OrganizerProfileController::class, 'update'])->name('organizer.profile.update');
});
