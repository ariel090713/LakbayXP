<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                // Existing user — update google_id if missing
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                // New user — create as organizer (pending verification)
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'username' => Str::slug(explode('@', $googleUser->getEmail())[0]) . '-' . Str::random(4),
                    'google_id' => $googleUser->getId(),
                    'avatar_path' => $googleUser->getAvatar(),
                    'role' => UserRole::Organizer,
                    'is_verified_organizer' => false,
                    'email_verified_at' => now(), // Google accounts are pre-verified
                ]);
            }

            // Ensure Google users always have verified email
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            Auth::login($user, true);

            if ($user->role === UserRole::Admin) {
                return redirect()->route('admin.dashboard');
            }

            // Redirect to onboarding if not completed
            if (!$user->onboarding_completed) {
                return redirect()->route('organizer.onboarding');
            }

            return redirect()->route('organizer.dashboard');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('status', 'Google sign-in failed. Please try again.');
        }
    }
}
