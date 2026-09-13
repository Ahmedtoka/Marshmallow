<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_visible' => 'boolean', 'is_featured' => 'boolean'];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderByDesc('is_featured')->orderBy('sort_order');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
