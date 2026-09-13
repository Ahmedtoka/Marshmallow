<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Photo extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_featured' => 'boolean'];

    protected static function booted(): void
    {
        static::deleted(fn (Photo $photo) => Media::delete($photo->path));
    }

    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(): string
    {
        return Media::url($this->path);
    }
}
