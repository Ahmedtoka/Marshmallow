<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = ['properties' => 'array', 'created_at' => 'datetime'];

    /** Human labels for the events the site tracks. */
    public const NAMES = [
        'cta_click' => 'Button clicked',
        'call_click' => 'Tapped call',
        'whatsapp_click' => 'Tapped WhatsApp',
        'map_click' => 'Opened map',
        'email_click' => 'Tapped email',
        'class_finder' => 'Used class finder',
        'section_view' => 'Viewed section',
        'form_start' => 'Started form',
        'form_submit' => 'Submitted form',
        'form_error' => 'Form error',
        'gallery_open' => 'Opened photo',
        'outbound_click' => 'Left to external link',
        'video_play' => 'Played video',
        'faq_open' => 'Opened FAQ',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function label(): string
    {
        return self::NAMES[$this->name] ?? ucfirst(str_replace('_', ' ', $this->name));
    }
}
