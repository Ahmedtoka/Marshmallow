<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['meta' => 'array'];

    public const TYPES = [
        'created' => 'Lead created',
        'note' => 'Note',
        'call' => 'Call',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
        'status_changed' => 'Status changed',
        'assigned' => 'Assigned',
        'follow_up_scheduled' => 'Follow-up scheduled',
        'follow_up_done' => 'Follow-up done',
        'updated' => 'Details updated',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }
}
