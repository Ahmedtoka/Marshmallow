<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A server-side event sent (or being sent) to the Meta Conversions API. */
class MetaConversion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'fields' => 'array',
        'test_event' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public const STATUSES = [
        'queued' => 'Waiting to send',
        'retrying' => 'Retrying',
        'sent' => 'Sent',
        'failed' => 'Failed',
    ];

    public const STATUS_BADGES = [
        'queued' => 'badge-muted',
        'retrying' => 'bg-honey/15 text-[#8A5D00]',
        'sent' => 'badge-green',
        'failed' => 'badge-red',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class)->withTrashed();
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
