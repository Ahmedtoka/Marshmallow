<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SeoPage extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('seo-pages'));
    }

    public static function for(string $key): ?SeoPage
    {
        return Cache::rememberForever('seo-pages', fn () => static::all()->keyBy('page_key'))->get($key);
    }
}
