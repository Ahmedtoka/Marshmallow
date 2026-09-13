<?php

namespace App\Models\Concerns;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPhotos
{
    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable')->orderBy('sort_order')->orderBy('id');
    }

    public static function bootHasPhotos(): void
    {
        static::deleting(function ($model) {
            $model->photos()->get()->each->delete();
        });
    }
}
