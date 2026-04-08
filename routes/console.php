<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// AI Place Discovery — runs every 10 minutes, discovers 5 new places
Schedule::command('places:discover --count=5')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/places-discover.log'));

// AI Place Update — runs every hour (offset 30 min), updates 10 places (no photos)
Schedule::command('places:update --limit=10')
    ->hourlyAt(30)
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/places-update.log'));
