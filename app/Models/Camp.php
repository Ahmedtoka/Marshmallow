<?php

namespace App\Models;

use App\Models\Concerns\HasPhotos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Camp extends Model
{
    use HasPhotos;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'activities' => 'array',
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public const SEASONS = ['summer' => 'Summer', 'winter' => 'Winter', 'spring' => 'Spring', 'autumn' => 'Autumn'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order');
    }

    public function seasonLabel(): string
    {
        return self::SEASONS[$this->season] ?? ucfirst($this->season);
    }

    public function datesLabel(): ?string
    {
        if (! $this->starts_on) {
            return null;
        }

        return $this->starts_on->format('j M').($this->ends_on ? ' – '.$this->ends_on->format('j M Y') : '');
    }
}
