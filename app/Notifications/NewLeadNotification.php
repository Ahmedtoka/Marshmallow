<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadNotification extends Notification
{
    public function __construct(public Lead $lead)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (config('mail.default') !== 'log' && Setting::get('lead_email_alerts', '1') === '1' && $notifiable->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'new_lead',
            'lead_id' => $this->lead->id,
            'title' => 'New lead: '.$this->lead->parent_name,
            'body' => collect([
                Lead::INTERESTS[$this->lead->interest] ?? null,
                $this->lead->classroom?->name,
                $this->lead->branch?->name,
            ])->filter()->implode(' · '),
            'url' => route('admin.crm.leads.show', $this->lead),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;

        return (new MailMessage)
            ->subject('New lead '.$lead->reference.': '.$lead->parent_name)
            ->greeting('New lead from the website')
            ->line('Parent: '.$lead->parent_name.' — '.$lead->phone)
            ->line('Child: '.trim(($lead->child_name ?? '').' '.($lead->childAgeLabel() ? '('.$lead->childAgeLabel().')' : '')))
            ->line('Class: '.($lead->classroom?->name ?? '—').' · Branch: '.($lead->branch?->name ?? '—'))
            ->line('Interested in: '.(Lead::INTERESTS[$lead->interest] ?? $lead->interest))
            ->action('Open lead', route('admin.crm.leads.show', $lead));
    }
}
