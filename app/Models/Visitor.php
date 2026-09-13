<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['first_seen_at' => 'datetime', 'last_seen_at' => 'datetime'];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class)->latest('started_at');
    }

    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
