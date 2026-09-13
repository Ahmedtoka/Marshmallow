<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Admin\Crm\Concerns\FiltersLeads;
use App\Http\Controllers\Admin\Crm\Concerns\SchedulesFollowUps;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    use FiltersLeads, SchedulesFollowUps;

    public const TABS = ['overdue' => 'Overdue', 'today' => 'Today', 'upcoming' => 'Upcoming', 'completed' => 'Completed'];

    public function __construct(private LeadService $leads)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : null;

        $base = FollowUp::query()->visibleTo($user)
            ->whereHas('lead', fn (Builder $q) => $q->visibleTo($user)
                ->when(! $user->isSales() && $request->integer('branch'), fn ($q) => $q->where('branch_id', $request->integer('branch'))))
            ->when(! $user->isSales() && $request->integer('agent'), fn ($q) => $q->where('user_id', $request->integer('agent')));

        $now = now();
        $endOfDay = now()->endOfDay();
        $row = (clone $base)->toBase()->selectRaw('
            SUM(CASE WHEN completed_at IS NULL AND due_at < ? THEN 1 ELSE 0 END) AS overdue,
            SUM(CASE WHEN completed_at IS NULL AND due_at >= ? AND due_at <= ? THEN 1 ELSE 0 END) AS today,
            SUM(CASE WHEN completed_at IS NULL AND due_at > ? THEN 1 ELSE 0 END) AS upcoming,
            SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) AS completed', [$now, $now, $endOfDay, $endOfDay])->first();
        $counts = collect(self::TABS)->map(fn ($l, $k) => (int) ($row->{$k} ?? 0))->all();

        // Land on the tab that needs attention first.
        $tab ??= $counts['overdue'] ? 'overdue' : 'today';

        $query = (clone $base)->with(['lead.classroom:id,name,color', 'lead.branch:id,name,short_name', 'user:id,name']);
        match ($tab) {
            'overdue' => $query->pending()->where('due_at', '<', $now)->orderBy('due_at'),
            'today' => $query->pending()->whereBetween('due_at', [$now, $endOfDay])->orderBy('due_at'),
            'upcoming' => $query->pending()->where('due_at', '>', $endOfDay)->orderBy('due_at'),
            'completed' => $query->whereNotNull('completed_at')->orderByDesc('completed_at'),
        };

        return view('admin.crm.follow-ups.index', [
            'followUps' => $query->paginate(30)->withQueryString(),
            'tab' => $tab,
            'counts' => $counts,
            'isManager' => ! $user->isSales(),
            'agents' => $user->isSales() ? [] : $this->assignableUsers()->pluck('name', 'id')->all(),
            'branches' => Branch::orderBy('sort_order')->pluck('name', 'id')->all(),
        ]);
    }

    public function store(Request $request, Lead $lead)
    {
        $this->ensureVisible($request, $lead);

        $data = $request->validate($this->followUpRules(), ['due_at.required' => 'Pick when to follow up.']);
        $followUp = $this->scheduleFollowUp($lead, $data, $request->user());

        return back()->with('success', 'Follow-up set for '.$followUp->due_at->format('D j M, g:i A').'.');
    }

    public function complete(Request $request, FollowUp $followUp)
    {
        $lead = $this->visibleLead($request, $followUp);
        $user = $request->user();

        $data = $request->validate([
            'outcome' => ['nullable', 'string', 'max:2000'],
            'next.schedule' => ['nullable', 'boolean'],
            ...$this->followUpRules('next.', true),
        ], ['next.due_at.required_if_accepted' => 'Pick when to follow up next.']);

        if (! $followUp->completed_at) {
            $followUp->update(['completed_at' => now(), 'outcome' => $data['outcome'] ?? null]);
            $this->leads->log($lead, 'follow_up_done', $data['outcome'] ?? null, [
                'follow_up_id' => $followUp->id,
                'type' => $followUp->type,
            ], $user);
            if (in_array($followUp->type, ['call', 'whatsapp', 'email'], true)) {
                $lead->update(['last_contacted_at' => now()]);
            }
            $this->leads->syncNextFollowUp($lead);
        }

        $message = 'Follow-up completed.';
        if ($request->boolean('next.schedule')) {
            $next = $this->scheduleFollowUp($lead, $data['next'], $user);
            $message .= ' Next one set for '.$next->due_at->format('D j M, g:i A').'.';
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, FollowUp $followUp)
    {
        $lead = $this->visibleLead($request, $followUp);
        $followUp->delete();
        $this->leads->syncNextFollowUp($lead);

        return back()->with('success', 'Follow-up removed.');
    }

    private function visibleLead(Request $request, FollowUp $followUp): Lead
    {
        $lead = $followUp->lead;
        abort_unless($lead, 404);
        $this->ensureVisible($request, $lead);

        return $lead;
    }
}
