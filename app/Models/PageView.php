<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['entered_at' => 'datetime'];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
