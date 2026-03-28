<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quotes()->random());
})->purpose('Display an inspiring quote');

// Schedule the daily digest command
app(Schedule::class)
    ->command('casa:send-digest')
    ->dailyAt('08:00')
    ->name('send-daily-digest')
    ->onOneServer();
