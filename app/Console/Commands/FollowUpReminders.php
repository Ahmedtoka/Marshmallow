<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Notifications\FollowUpDueNotification;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class FollowUpReminders extends Command
{
    protected $signature = 'crm:follow-up-reminders {--minutes=60 : Remind about follow-ups due within this many minutes}';

    protected $description = 'Notify sales agents about follow-ups that are due soon';

    public function handle(): int
    {
        $window = max(1, (int) $this->option('minutes'));

        $followUps = FollowUp::pending()
            ->whereBetween('due_at', [now(), now()->addMinutes($window)])
            ->whereNotNull('user_id')
            ->whereHas('lead', fn ($q) => $q->open())
            ->with(['lead', 'user'])
            ->get();

        $sent = 0;
        foreach ($followUps as $followUp) {
            if (! $followUp->user?->is_active) {
                continue;
            }

            $already = DatabaseNotification::query()
                ->where('type', FollowUpDueNotification::class)
                ->where('notifiable_type', $followUp->user->getMorphClass())
                ->where('notifiable_id', $followUp->user_id)
                ->where('data', 'like', '%"follow_up_id":'.$followUp->id.',%')
                ->exists();

            if ($already) {
                continue;
            }

            $followUp->user->notify(new FollowUpDueNotification($followUp));
            $sent++;
        }

        $this->info("Sent {$sent} reminder(s) for {$followUps->count()} follow-up(s) due in the next {$window} minutes.");

        return self::SUCCESS;
    }
}
