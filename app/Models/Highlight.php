<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Highlight extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_visible' => 'boolean'];

    public const GROUPS = [
        'why' => 'Why Marshmallow',
        'safety' => 'Safety & security',
        'health' => 'Health & hygiene',
        'meals' => 'Meals',
        'logistics' => 'Transport & communication',
        'credentials' => 'Credentials & training',
        'services' => 'Services & hours',
    ];

    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group)->where('is_visible', true)->orderBy('sort_order');
    }
}
