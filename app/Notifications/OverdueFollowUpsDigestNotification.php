<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class OverdueFollowUpsDigestNotification extends Notification
{
    public function __construct(public int $overdue, public int $today = 0)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'follow_up_digest',
            'date' => now()->toDateString(),
            'title' => $this->overdue === 1 ? 'You have 1 overdue follow-up' : 'You have '.$this->overdue.' overdue follow-ups',
            'body' => $this->today ? $this->today.' more due today. Start with the oldest.' : 'Start with the oldest one.',
            'url' => route('admin.crm.follow-ups.index', ['tab' => 'overdue']),
        ];
    }
}
