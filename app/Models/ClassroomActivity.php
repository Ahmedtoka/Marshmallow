<?php

namespace App\Models;

use App\Models\Concerns\HasPhotos;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ClassroomActivity extends Pivot
{
    use HasPhotos;

    protected $table = 'classroom_activity';

    public $incrementing = true;

    protected $guarded = ['id'];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
