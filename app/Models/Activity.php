<?php

namespace App\Models;

use App\Models\Concerns\HasPhotos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Activity extends Model
{
    use HasPhotos;

    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    public const CATEGORIES = [
        'academic' => 'Academics',
        'languages' => 'Languages',
        'movement' => 'Movement & sports',
        'creative' => 'Art & music',
        'science' => 'Science & discovery',
        'life' => 'Life skills',
        'outings' => 'Trips & events',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_activity')
            ->using(ClassroomActivity::class)
            ->withPivot(['id', 'frequency', 'details', 'sort_order'])
            ->orderBy('min_months');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
