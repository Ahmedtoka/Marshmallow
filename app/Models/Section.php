<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Section extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_visible' => 'boolean'];

    public const CACHE_KEY = 'site-sections';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** @return \Illuminate\Support\Collection<string, Section> */
    public static function map()
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::orderBy('sort_order')->get()->keyBy('key'));
    }

    public static function for(string $key): ?Section
    {
        return static::map()->get($key);
    }
}
