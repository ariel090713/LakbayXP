<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FirebaseEmailAuthController extends Controller
{
    /**
     * Register organizer with email/password via Firebase Auth.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $firebaseAuth = app('firebase.auth');

            // Create user in Firebase
            $firebaseUser = $firebaseAuth->createUserWithEmailAndPassword(
                $request->email,
                $request->password
            );

            // Send email verification via Firebase
            $firebaseAuth->sendEmailVerificationLink($request->email);

            $firebaseUid = $firebaseUser->uid;

            // Check if local user exists
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'username' => Str::slug(explode('@', $request->email)[0]) . '-' . Str::random(4),
                    'firebase_uid' => $firebaseUid,
                    'role' => UserRole::Organizer,
                    'is_verified_organizer' => false,
                    'email_verified_at' => null,
                ]);
            } else {
                $user->update(['firebase_uid' => $firebaseUid]);
            }

            Auth::login($user, true);

            return redirect()->route('organizer.dashboard');
        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            return back()->withErrors(['email' => 'This email is already registered. Try signing in instead.'])->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Login organizer with email/password via Firebase Auth.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $firebaseAuth = app('firebase.auth');

            // Verify credentials with Firebase
            $signInResult = $firebaseAuth->signInWithEmailAndPassword(
                $request->email,
                $request->password
            );

            $firebaseUid = $signInResult->firebaseUserId();

            // Find local user
            $user = User::where('firebase_uid', $firebaseUid)
                ->orWhere('email', $request->email)
                ->first();

            if (!$user) {
                return back()->withErrors(['email' => 'No organizer account found.'])->withInput();
            }

            // Update firebase_uid if missing
            if (!$user->firebase_uid) {
                $user->update(['firebase_uid' => $firebaseUid]);
            }

            Auth::login($user, $request->boolean('remember'));

            if ($user->role === UserRole::Admin) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('organizer.dashboard');
        } catch (\Kreait\Firebase\Exception\Auth\InvalidPassword $e) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            return back()->withErrors(['email' => 'No account found with this email.'])->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Login failed. Please try again.'])->withInput();
        }
    }
}
