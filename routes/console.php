<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// AI Place Discovery — runs every hour, discovers 5 new places with photos
Schedule::command('places:discover --count=5 --with-photos')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/places-discover.log'));

// AI Place Update — runs every hour (offset 30 min), updates 10 places
Schedule::command('places:update --limit=10 --photos')
    ->hourlyAt(30)
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/places-update.log'));
