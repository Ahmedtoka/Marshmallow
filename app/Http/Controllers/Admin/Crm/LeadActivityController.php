<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Admin\Crm\Concerns\FiltersLeads;
use App\Http\Controllers\Admin\Crm\Concerns\SchedulesFollowUps;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadActivityController extends Controller
{
    use FiltersLeads, SchedulesFollowUps;

    public function store(Request $request, Lead $lead, LeadService $service)
    {
        $this->ensureVisible($request, $lead);
        $user = $request->user();

        $data = $request->validate([
            'type' => ['required', Rule::in(['note', 'call', 'whatsapp', 'email'])],
            'body' => ['nullable', 'required_if:type,note', 'string', 'max:3000'],
            'mark_contacted' => ['nullable', 'boolean'],
            'follow_up.schedule' => ['nullable', 'boolean'],
            ...$this->followUpRules('follow_up.', true),
        ], [
            'body.required_if' => 'Write the note first.',
            'follow_up.due_at.required_if_accepted' => 'Pick when to follow up.',
            'follow_up.type.required_if_accepted' => 'Pick the follow-up type.',
        ]);

        $service->log($lead, $data['type'], $data['body'] ?? null, [], $user);

        $messages = ['Activity logged.'];

        if ($request->boolean('mark_contacted') && $lead->status === 'new' && $data['type'] !== 'note') {
            $service->changeStatus($lead, 'contacted', $user);
            $messages[] = 'Marked as contacted.';
        }

        if ($request->boolean('follow_up.schedule')) {
            $followUp = $this->scheduleFollowUp($lead, $data['follow_up'], $user);
            $messages[] = 'Follow-up set for '.$followUp->due_at->format('D j M, g:i A').'.';
        }

        return redirect()->route('admin.crm.leads.show', $lead)->with('success', implode(' ', $messages));
    }
}
