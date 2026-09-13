<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification
{
    public function __construct(public Lead $lead, public User $by)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'lead_assigned',
            'lead_id' => $this->lead->id,
            'title' => $this->by->name.' assigned you a lead',
            'body' => $this->lead->parent_name.' · '.$this->lead->phone,
            'url' => route('admin.crm.leads.show', $this->lead),
        ];
    }
}
