<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Heartbeat for the Cloudways cron. Follow-up reminders, the morning digest and the Meta Conversions API
 * all depend on `schedule:run` every minute; when the cron is missing nothing fails loudly, so the
 * scheduler stamps the time on every run and the dashboard warns when the stamp gets old.
 */
class Scheduler
{
    private const KEY = 'scheduler:last_run';

    /** The cron runs every minute; a few missed minutes can be a slow run, more means it has stopped. */
    public const STALE_MINUTES = 5;

    public static function beat(): void
    {
        Cache::forever(self::KEY, now()->toIso8601String());
    }

    public static function lastRun(): ?CarbonInterface
    {
        $value = Cache::get(self::KEY);

        return $value ? Carbon::parse($value) : null;
    }

    /** Null (never seen, e.g. just after a deploy cleared the cache) counts as not running. */
    public static function isRunning(): bool
    {
        $last = self::lastRun();

        return $last !== null && $last->gt(now()->subMinutes(self::STALE_MINUTES));
    }
}
