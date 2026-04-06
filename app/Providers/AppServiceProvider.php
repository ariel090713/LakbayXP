<?php

namespace App\Providers;

use App\Services\NotificationService;
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
