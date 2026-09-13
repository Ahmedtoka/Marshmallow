<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Models\User;
use App\Notifications\OverdueFollowUpsDigestNotification;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class FollowUpDigest extends Command
{
    protected $signature = 'crm:follow-up-digest';

    protected $description = 'Morning digest for every agent who has overdue follow-ups';

    public function handle(): int
    {
        $rows = FollowUp::pending()->whereNotNull('user_id')->whereHas('lead')
            ->where('due_at', '<=', now()->endOfDay())
            ->toBase()
            ->selectRaw('user_id, SUM(CASE WHEN due_at < ? THEN 1 ELSE 0 END) AS overdue, SUM(CASE WHEN due_at >= ? THEN 1 ELSE 0 END) AS today', [now(), now()])
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $sent = 0;
        foreach (User::active()->whereIn('id', $rows->keys())->get() as $user) {
            $row = $rows[$user->id];
            if ((int) $row->overdue === 0) {
                continue;
            }

            $already = DatabaseNotification::query()
                ->where('type', OverdueFollowUpsDigestNotification::class)
                ->where('notifiable_type', $user->getMorphClass())
                ->where('notifiable_id', $user->id)
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();
            if ($already) {
                continue;
            }

            $user->notify(new OverdueFollowUpsDigestNotification((int) $row->overdue, (int) $row->today));
            $sent++;
        }

        $this->info("Sent {$sent} digest(s).");

        return self::SUCCESS;
    }
}
