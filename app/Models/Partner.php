<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_visible' => 'boolean'];

    public const TYPES = ['school' => 'Partner school', 'certification' => 'Certification / training', 'award' => 'Recognition'];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort_order');
    }
}
