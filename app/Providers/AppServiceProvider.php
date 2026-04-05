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
        // This allows Laravel Cloud (env var only) to work without uploading files.
        $creds = env('FIREBASE_CREDENTIALS');
        if ($creds) {
            $trimmed = trim($creds);

            // If it's JSON
            if (str_starts_with($trimmed, '{')) {
                $json = $trimmed;
            }
            // If it's base64 (not a file path, not JSON)
            elseif (!str_contains($trimmed, '/') && !str_contains($trimmed, '.json')) {
                $decoded = base64_decode($trimmed, true);
                if ($decoded && str_starts_with(trim($decoded), '{')) {
                    $json = $decoded;
                }
            }

            if (isset($json)) {
                $tempPath = storage_path('framework/firebase-credentials.json');
                if (!file_exists($tempPath) || file_get_contents($tempPath) !== $json) {
                    file_put_contents($tempPath, $json);
                }
                config(['firebase.projects.app.credentials' => $tempPath]);
            }
        }
    }
}
