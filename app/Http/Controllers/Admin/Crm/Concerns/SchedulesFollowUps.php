<?php

namespace App\Http\Controllers\Admin\Crm\Concerns;

use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

trait SchedulesFollowUps
{
    /** Validation rules for a nested follow-up (prefix e.g. "next." or ""). */
    protected function followUpRules(string $prefix = '', bool $optional = false): array
    {
        $required = $optional ? 'required_if_accepted:'.$prefix.'schedule' : 'required';

        return [
            $prefix.'type' => [$required, 'nullable', Rule::in(array_keys(FollowUp::TYPES))],
            $prefix.'due_at' => [$required, 'nullable', 'date'],
            $prefix.'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function scheduleFollowUp(Lead $lead, array $data, User $by): FollowUp
    {
        $followUp = $lead->followUps()->create([
            'user_id' => $lead->assigned_to ?: $by->id,
            'created_by' => $by->id,
            'type' => $data['type'],
            'due_at' => Carbon::parse($data['due_at']),
            'notes' => $data['notes'] ?? null,
        ]);

        $service = app(LeadService::class);
        $service->log($lead, 'follow_up_scheduled', $data['notes'] ?? null, [
            'follow_up_id' => $followUp->id,
            'type' => $followUp->type,
            'due_at' => $followUp->due_at->toDateTimeString(),
        ], $by);
        $service->syncNextFollowUp($lead);

        return $followUp;
    }
}
