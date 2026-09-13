<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['due_at' => 'datetime', 'completed_at' => 'datetime'];

    public const TYPES = ['call' => 'Call', 'whatsapp' => 'WhatsApp', 'tour' => 'School tour', 'meeting' => 'Meeting', 'email' => 'Email'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSales()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public function isOverdue(): bool
    {
        return ! $this->completed_at && $this->due_at->isPast();
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
