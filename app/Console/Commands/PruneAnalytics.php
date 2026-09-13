<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Deletes old raw tracking data. Visits that produced a lead are kept so lead journeys stay complete.
 * Schedule in routes/console.php: Schedule::command('analytics:prune')->weekly();
 */
class PruneAnalytics extends Command
{
    protected $signature = 'analytics:prune {--days=400 : Keep this many days of visits}';

    protected $description = 'Delete visits, page views and events older than N days (visits that became leads are kept)';

    public function handle(): int
    {
        $days = max(30, (int) $this->option('days'));
        $cutoff = now()->subDays($days)->toDateTimeString();
        $deleted = 0;

        // page_views and tracking_events cascade from visits.
        do {
            $ids = DB::table('visits')->where('started_at', '<', $cutoff)->where('converted', false)->limit(2000)->pluck('id');
            if ($ids->isEmpty()) {
                break;
            }
            DB::table('tracking_events')->whereIn('visit_id', $ids)->delete();
            DB::table('page_views')->whereIn('visit_id', $ids)->delete();
            $deleted += DB::table('visits')->whereIn('id', $ids)->delete();
        } while (true);

        $visitors = DB::table('visitors')
            ->whereNull('lead_id')
            ->where('last_seen_at', '<', $cutoff)
            ->whereNotExists(fn ($q) => $q->from('visits')->whereColumn('visits.visitor_id', 'visitors.id'))
            ->delete();

        $this->info("Deleted {$deleted} visits (with their page views and events) and {$visitors} visitors older than {$days} days.");

        return self::SUCCESS;
    }
}
