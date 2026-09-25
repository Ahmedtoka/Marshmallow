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

// Queue worker without a separate supervisor: each minute the cron starts a worker that sends whatever is
// waiting (Meta Conversions API events) and exits. In the background so it never delays the jobs above.
Schedule::command('queue:work --stop-when-empty --tries=5 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();
