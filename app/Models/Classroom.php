<?php

namespace App\Models;

use App\Models\Concerns\HasPhotos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasPhotos;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'goals' => 'array',
        'daily_routine' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('min_months');
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'classroom_activity')
            ->using(ClassroomActivity::class)
            ->withPivot(['id', 'frequency', 'details', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function classroomActivities(): HasMany
    {
        return $this->hasMany(ClassroomActivity::class)->orderBy('sort_order');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** Ranges are [min, max): a child exactly on the upper boundary moves up to the next class. */
    public function fitsAge(int $months): bool
    {
        return $months >= $this->min_months && ($this->max_months === null || $months < $this->max_months);
    }

    public function ageRangeLabel(): string
    {
        if ($this->age_label) {
            return $this->age_label;
        }

        $to = $this->max_months === null ? 'school age' : self::monthsLabel($this->max_months);

        return self::monthsLabel($this->min_months).' – '.$to;
    }

    public static function monthsLabel(int $months): string
    {
        if ($months < 12) {
            return $months.' months';
        }

        $years = $months / 12;
        $formatted = rtrim(rtrim(number_format($years, 1), '0'), '.');

        return $formatted.($years == 1 ? ' year' : ' years');
    }
}
