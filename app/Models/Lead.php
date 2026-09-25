<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'child_dob' => 'date',
        'preferred_tour_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'last_contacted_at' => 'datetime',
        'enrolled_at' => 'datetime',
    ];

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'tour_booked' => 'Tour booked',
        'toured' => 'Toured',
        'enrolled' => 'Enrolled',
        'lost' => 'Not interested',
    ];

    public const STATUS_COLORS = [
        'new' => '#2CBCC9',
        'contacted' => '#8479BD',
        'tour_booked' => '#E8A317',
        'toured' => '#E8177F',
        'enrolled' => '#7FA82A',
        'lost' => '#9B98B8',
    ];

    public const INTERESTS = [
        'enrollment' => 'Enrollment',
        'tour' => 'School tour',
        'camp' => 'Camp',
        'waitlist' => 'Waitlist',
        'general' => 'General question',
    ];

    public const PRIORITIES = ['hot' => 'Hot', 'warm' => 'Warm', 'cold' => 'Cold'];

    public const CHANNELS = [
        'website' => 'Website form',
        'phone' => 'Phone call',
        'whatsapp' => 'WhatsApp',
        'facebook' => 'Facebook / Messenger',
        'walk_in' => 'Walk-in',
        'referral' => 'Referral',
    ];

    public const LOST_REASONS = ['Price', 'Location / too far', 'Chose another nursery', 'Child too young', 'No response', 'Timing / schedule', 'Other'];

    protected static function booted(): void
    {
        static::created(function (Lead $lead) {
            if (! $lead->reference) {
                $lead->forceFill(['reference' => 'MM-'.str_pad((string) $lead->id, 5, '0', STR_PAD_LEFT)])->saveQuietly();
            }
        });
    }

    /** Server-side events sent to Meta for this booking (Lead, and Schedule for a visit). */
    public function metaConversions(): HasMany
    {
        return $this->hasMany(MetaConversion::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest('id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class)->orderBy('due_at');
    }

    public function visitor(): HasOne
    {
        return $this->hasOne(Visitor::class, 'uuid', 'visitor_uuid');
    }

    /** Sales agents only ever see their own leads. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSales()) {
            $query->where('assigned_to', $user->id);
        }

        return $query;
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['enrolled', 'lost']);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? '#9B98B8';
    }

    public function whatsappLink(?string $text = null): string
    {
        $number = preg_replace('/\D/', '', $this->whatsapp ?: $this->phone);
        if (str_starts_with($number, '0')) {
            $number = '2'.$number;
        }

        return 'https://wa.me/'.$number.($text ? '?text='.rawurlencode($text) : '');
    }

    public function childAgeLabel(): ?string
    {
        if ($this->child_age_months === null) {
            return null;
        }

        $years = intdiv($this->child_age_months, 12);
        $months = $this->child_age_months % 12;

        return trim(($years ? $years.'y ' : '').($months ? $months.'m' : ''));
    }

    public function isOverdue(): bool
    {
        return $this->next_follow_up_at && $this->next_follow_up_at->isPast() && ! in_array($this->status, ['enrolled', 'lost']);
    }
}
