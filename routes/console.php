<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// AI Place Discovery — runs daily at 2 AM, discovers 10 new places with photos
Schedule::command('places:discover --count=10 --with-photos')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/places-discover.log'));

// AI Place Update — runs daily at 3 AM, updates 20 places with correct data + missing photos
Schedule::command('places:update --limit=20 --photos')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/places-update.log'));
