<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $guarded = ['id'];

    public const STATUSES = ['new' => 'New', 'reviewed' => 'Reviewed', 'shortlisted' => 'Shortlisted', 'hired' => 'Hired', 'rejected' => 'Rejected'];

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
