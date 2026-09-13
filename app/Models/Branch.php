<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function whatsappLink(?string $text = null): string
    {
        $number = preg_replace('/\D/', '', $this->whatsapp ?: $this->phone);
        if (str_starts_with($number, '0')) {
            $number = '2'.$number;
        }

        return 'https://wa.me/'.$number.($text ? '?text='.rawurlencode($text) : '');
    }

    public function telLink(): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', $this->phone);
    }
}
