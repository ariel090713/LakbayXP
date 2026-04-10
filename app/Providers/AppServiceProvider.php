<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Kreait\Firebase\Contract\Messaging;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NotificationService::class, function ($app) {
            try {
                $messaging = $app->make(Messaging::class);
            } catch (\Throwable) {
                $messaging = null;
            }

            return new NotificationService($messaging);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Rate Limiters ──

        // Default API: 120 requests/min per user (or IP if unauthenticated)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Auth endpoints: 10/min per IP (prevent brute force)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Write operations (posts, comments, reactions): 30/min per user
        RateLimiter::for('write', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Upload: 20/min per user
        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        // Booking: 10/min per user (prevent spam booking)
        RateLimiter::for('booking', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Follow/buddy: 30/min per user
        RateLimiter::for('social', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Heavy reads (leaderboard, explorers): 60/min per user
        RateLimiter::for('heavy', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        // Support Firebase credentials as: file path, JSON string, or base64-encoded JSON.
        $creds = env('FIREBASE_CREDENTIALS');
        if ($creds) {
            $trimmed = trim($creds);
            $json = null;

            // If it's a file path that exists, let kreait handle it
            if (file_exists($trimmed)) {
                return;
            }

            // If it starts with { it's raw JSON
            if (str_starts_with($trimmed, '{')) {
                $json = $trimmed;
            }

            // Otherwise try base64 decode
            if (!$json) {
                $decoded = base64_decode($trimmed, true);
                if ($decoded && str_starts_with(trim($decoded), '{')) {
                    $json = trim($decoded);
                }
            }

            if ($json) {
                // Validate it's actually valid JSON
                $parsed = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($parsed['project_id'])) {
                    $tempPath = storage_path('framework/firebase-credentials.json');
                    file_put_contents($tempPath, $json);
                    config(['firebase.projects.app.credentials' => $tempPath]);
                } else {
                    \Log::error('Firebase credentials: decoded but invalid JSON', [
                        'json_error' => json_last_error_msg(),
                        'starts_with' => substr($json, 0, 50),
                    ]);
                }
            } else {
                \Log::error('Firebase credentials: could not decode', [
                    'length' => strlen($trimmed),
                    'starts_with' => substr($trimmed, 0, 20),
                ]);
            }
        }
    }
}
