<?php

namespace App\Models;

use App\Models\Concerns\HasPhotos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryAlbum extends Model
{
    use HasPhotos;

    protected $guarded = ['id'];

    protected $casts = ['is_visible' => 'boolean', 'event_date' => 'date'];

    public const CATEGORIES = [
        'activities' => 'Activities',
        'celebrations' => 'Celebrations',
        'graduation' => 'Graduation',
        'trips' => 'Trips',
        'camps' => 'Camps',
        'campus' => 'Our campus',
        'reviews' => 'Parent reviews',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort_order')->orderByDesc('event_date');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
