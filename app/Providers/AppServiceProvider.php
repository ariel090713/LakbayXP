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
        //
    }
}
