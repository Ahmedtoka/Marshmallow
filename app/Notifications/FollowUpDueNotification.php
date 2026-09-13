<?php

namespace App\Notifications;

use App\Models\FollowUp;
use Illuminate\Notifications\Notification;

class FollowUpDueNotification extends Notification
{
    public function __construct(public FollowUp $followUp)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $lead = $this->followUp->lead;

        return [
            'kind' => 'follow_up_due',
            'follow_up_id' => $this->followUp->id,
            'lead_id' => $lead->id,
            'title' => $this->followUp->typeLabel().' '.$lead->parent_name.' at '.$this->followUp->due_at->format('g:i A'),
            'body' => collect([$lead->child_name, $lead->phone, $this->followUp->notes])->filter()->implode(' · '),
            'url' => route('admin.crm.leads.show', $lead),
        ];
    }
}
