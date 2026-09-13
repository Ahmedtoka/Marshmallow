<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'is_bounce' => 'boolean',
        'converted' => 'boolean',
    ];

    public const SOURCES = [
        'direct' => 'Direct',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'google' => 'Google search',
        'google_ads' => 'Google Ads',
        'meta_ads' => 'Meta Ads',
        'whatsapp' => 'WhatsApp',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'search' => 'Other search',
        'email' => 'Email',
        'referral' => 'Other websites',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class)->orderBy('entered_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('created_at');
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst($this->source);
    }
}
