<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FirebaseWebAuthController extends Controller
{
    /**
     * Handle Firebase Google sign-in for web organizers.
     * Receives a Firebase ID token from the client-side JS SDK,
     * verifies it, finds or creates an organizer user, and logs them in via session.
     */
    public function handleGoogleSignIn(Request $request): JsonResponse
    {
        $request->validate([
            'firebase_token' => ['required', 'string'],
            'mode' => ['required', 'in:login,register'],
        ]);

        try {
            $auth = app('firebase.auth');
            $verifiedToken = $auth->verifyIdToken($request->input('firebase_token'));

            $firebaseUid = $verifiedToken->claims()->get('sub');
            $email = $verifiedToken->claims()->get('email');
            $name = $verifiedToken->claims()->get('name') ?? $email;
            $googleId = $verifiedToken->claims()->get('firebase')['identities']['google.com'][0] ?? null;

            $mode = $request->input('mode');

            // Check if user already exists
            $existingUser = User::where('firebase_uid', $firebaseUid)
                ->orWhere('email', $email)
                ->first();

            if ($mode === 'login') {
                if (!$existingUser) {
                    return response()->json(['message' => 'No account found. Please register first.'], 404);
                }

                Auth::login($existingUser, true);
                $request->session()->regenerate();

                return response()->json([
                    'redirect' => $existingUser->role === UserRole::Admin
                        ? route('admin.dashboard')
                        : route('organizer.dashboard'),
                ]);
            }

            // Register mode
            if ($existingUser) {
                // User exists — just log them in
                if (!$existingUser->firebase_uid) {
                    $existingUser->update(['firebase_uid' => $firebaseUid, 'google_id' => $googleId]);
                }

                Auth::login($existingUser, true);
                $request->session()->regenerate();

                return response()->json([
                    'redirect' => $existingUser->role === UserRole::Admin
                        ? route('admin.dashboard')
                        : route('organizer.dashboard'),
                ]);
            }

            // Create new organizer
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'username' => Str::slug(explode('@', $email)[0]) . '-' . Str::random(4),
                'firebase_uid' => $firebaseUid,
                'google_id' => $googleId,
                'role' => UserRole::Organizer,
                'is_verified_organizer' => false,
            ]);

            Auth::login($user, true);
            $request->session()->regenerate();

            return response()->json([
                'redirect' => route('organizer.dashboard'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Firebase web auth failed', [
                'error' => $e->getMessage(),
                'mode' => $request->input('mode'),
            ]);

            return response()->json([
                'message' => 'Authentication failed: ' . $e->getMessage(),
            ], 401);
        }
    }
}
