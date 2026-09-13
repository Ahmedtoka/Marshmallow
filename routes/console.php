<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| CRM reminders (run `php artisan schedule:run` every minute from cron / Task Scheduler)
|--------------------------------------------------------------------------
*/
Schedule::command('crm:follow-up-reminders')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('crm:follow-up-digest')->dailyAt('08:00')->timezone('Africa/Cairo');

// Keep analytics tables lean; visits that became leads are kept.
Schedule::command('analytics:prune')->weekly();
