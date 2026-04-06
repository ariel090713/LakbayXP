<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Authenticate a mobile user via Firebase ID token and return a Sanctum token.
     *
     * POST /api/auth/firebase
     */
    public function firebaseLogin(Request $request): JsonResponse
    {
        $request->validate([
            'firebase_token' => ['required', 'string'],
        ]);

        try {
            $firebaseToken = $request->input('firebase_token');

            // Verify Firebase ID token via kreait/laravel-firebase
            $auth = app('firebase.auth');
            $verifiedToken = $auth->verifyIdToken($firebaseToken);

            $firebaseUid = $verifiedToken->claims()->get('sub');
            $email = $verifiedToken->claims()->get('email');
            $name = $verifiedToken->claims()->get('name') ?? $email;
            $googleId = $verifiedToken->claims()->get('firebase')['identities']['google.com'][0] ?? null;

            // Find by firebase_uid first, then by email (user may exist from web login)
            $user = User::where('firebase_uid', $firebaseUid)->first();

            if (!$user) {
                $user = User::where('email', $email)->first();

                if ($user) {
                    // Existing user (e.g. organizer who signed up via web) — link Firebase UID
                    $user->update([
                        'firebase_uid' => $firebaseUid,
                        'google_id' => $googleId ?? $user->google_id,
                    ]);
                } else {
                    // Brand new user
                    $user = User::create([
                        'firebase_uid' => $firebaseUid,
                        'name' => $name,
                        'email' => $email,
                        'username' => Str::slug(explode('@', $email)[0]) . '-' . Str::random(4),
                        'google_id' => $googleId,
                        'role' => UserRole::User,
                    ]);
                }
            }

            // Issue Sanctum token for API access
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            \Log::error('Firebase auth failed', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'token_length' => strlen($request->input('firebase_token', '')),
            ]);

            return response()->json([
                'message' => 'Invalid Firebase token.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 401);
        }
    }

    /**
     * Store or update the authenticated user's FCM token for push notifications.
     *
     * POST /api/auth/fcm-token
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => ['required', 'string'],
        ]);

        $request->user()->update([
            'fcm_token' => $request->input('fcm_token'),
        ]);

        return response()->json([
            'message' => 'FCM token updated',
        ]);
    }
}
