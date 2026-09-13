<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Admin\Crm\Concerns\FiltersLeads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\LeadRequest;
use App\Models\Branch;
use App\Models\Camp;
use App\Models\Classroom;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use App\Models\Visit;
use App\Services\LeadService;
use App\Support\ClassFinder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    use FiltersLeads;

    private const FIELD_LABELS = [
        'parent_name' => 'parent name', 'phone' => 'phone', 'whatsapp' => 'WhatsApp', 'email' => 'email',
        'child_name' => 'child name', 'child_dob' => "child's birthday", 'academic_year' => 'academic year',
        'classroom_id' => 'class', 'branch_id' => 'branch', 'interest' => 'interest', 'camp_id' => 'camp',
        'preferred_tour_at' => 'preferred tour date', 'heard_from' => 'heard from', 'message' => 'message',
        'channel' => 'channel', 'priority' => 'priority',
    ];

    public function __construct(private LeadService $leads)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $view = array_key_exists($request->query('view'), self::VIEWS) ? $request->query('view') : 'open';

        $base = $this->filteredLeads($request);
        $counts = $this->viewCounts($base);

        $query = $this->applyView(clone $base, $view)->with(['classroom:id,name,color,slug', 'branch:id,name,short_name', 'assignee:id,name']);
        $view === 'overdue' ? $query->orderBy('next_follow_up_at') : $query->latest('id');

        $leads = $query->paginate(25)->withQueryString();

        return view('admin.crm.leads.index', [
            'leads' => $leads,
            'view' => $view,
            'counts' => $counts,
            'options' => $this->filterOptions(),
            'isManager' => ! $user->isSales(),
            'filtersActive' => collect($request->only(['q', 'status', 'branch', 'class', 'interest', 'source', 'assignee', 'from', 'to']))->filter()->isNotEmpty(),
        ]);
    }

    public function board(Request $request)
    {
        $user = $request->user();
        $base = Lead::query()->visibleTo($user)
            ->when($request->integer('branch'), fn ($q, $b) => $q->where('branch_id', $b))
            ->when(array_key_exists((string) $request->query('interest'), Lead::INTERESTS), fn ($q) => $q->where('interest', $request->query('interest')))
            ->when(! $user->isSales() && $request->query('assignee'), function ($q) use ($request) {
                $request->query('assignee') === 'none' ? $q->whereNull('assigned_to') : $q->where('assigned_to', (int) $request->query('assignee'));
            });

        $counts = (clone $base)->toBase()->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status');

        $columns = [];
        foreach (Lead::STATUSES as $status => $label) {
            $columns[$status] = [
                'label' => $label,
                'color' => Lead::STATUS_COLORS[$status],
                'count' => (int) ($counts[$status] ?? 0),
                'leads' => (clone $base)->where('status', $status)->latest('id')->limit(50)
                    ->with(['classroom:id,name,color', 'branch:id,name,short_name', 'assignee:id,name'])->get(),
            ];
        }

        return view('admin.crm.leads.board', [
            'columns' => $columns,
            'options' => $this->filterOptions(),
            'isManager' => ! $user->isSales(),
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.crm.leads.create', $this->formData($request, new Lead([
            'branch_id' => $request->user()->branch_id,
            'interest' => 'enrollment',
            'priority' => 'warm',
            'channel' => 'phone',
            'academic_year' => ClassFinder::academicYears()[0],
        ])));
    }

    public function store(LeadRequest $request)
    {
        $user = $request->user();
        $data = $request->leadData();

        if (! $request->boolean('allow_duplicate') && ($duplicates = $this->duplicates($data['phone']))->isNotEmpty()) {
            return back()->withInput()->with('duplicates', $duplicates->map(fn (Lead $l) => $this->duplicateRow($l, $user))->all());
        }

        $data['assigned_to'] = $user->isSales() ? $user->id : ($request->input('assigned_to') ?: null);

        $lead = $this->leads->create($data, $user);

        return redirect()->route('admin.crm.leads.show', $lead)->with('success', 'Lead '.$lead->fresh()->reference.' added.');
    }

    public function show(Request $request, Lead $lead)
    {
        $this->ensureVisible($request, $lead);
        $user = $request->user();

        $lead->load([
            'classroom', 'branch', 'camp', 'assignee',
            'activities.user:id,name',
            'followUps.user:id,name',
            'visitor',
        ]);

        return view('admin.crm.leads.show', [
            'lead' => $lead,
            'isManager' => ! $user->isSales(),
            'agents' => $user->isSales() ? collect() : $this->assignableUsers(),
            'duplicates' => $this->duplicates($lead->phone, $lead->id)->map(fn (Lead $l) => $this->duplicateRow($l, $user)),
            'sourceLabel' => $lead->source ? (Visit::SOURCES[$lead->source] ?? ucfirst($lead->source)) : null,
            'pendingFollowUps' => $lead->followUps->whereNull('completed_at')->values(),
            'doneFollowUps' => $lead->followUps->whereNotNull('completed_at')->sortByDesc('completed_at')->values(),
            'users' => User::whereIn('id', $lead->activities->pluck('meta.to')->filter()->unique())->pluck('name', 'id'),
        ]);
    }

    public function edit(Request $request, Lead $lead)
    {
        $this->ensureVisible($request, $lead);

        return view('admin.crm.leads.edit', $this->formData($request, $lead));
    }

    public function update(Request $request, Lead $lead)
    {
        $this->ensureVisible($request, $lead);
        $user = $request->user();

        // Quick priority change from the lead page.
        if ($request->has('quick_priority')) {
            $data = $request->validate(['priority' => ['required', Rule::in(array_keys(Lead::PRIORITIES))]]);
            if ($lead->priority !== $data['priority']) {
                $from = $lead->priority;
                $lead->update($data);
                $this->leads->log($lead, 'updated', 'Priority: '.(Lead::PRIORITIES[$from] ?? $from).' → '.Lead::PRIORITIES[$data['priority']], ['fields' => ['priority']], $user);
            }

            return back()->with('success', 'Priority updated.');
        }

        /** @var LeadRequest $form */
        $form = app(LeadRequest::class);
        $data = $form->leadData();

        $dobChanged = ($lead->child_dob?->toDateString()) !== ($data['child_dob'] ? Carbon::parse($data['child_dob'])->toDateString() : null);
        $yearChanged = ($data['academic_year'] ?? null) !== $lead->academic_year;
        if ($dobChanged || $yearChanged) {
            if (! empty($data['child_dob'])) {
                $data['academic_year'] ??= ClassFinder::academicYears()[0];
                $result = ClassFinder::find(Carbon::parse($data['child_dob']), $data['academic_year']);
                $data['child_age_months'] = $result['months'];
                $data['classroom_id'] = $result['classroom']?->id;
            } else {
                $data['child_age_months'] = null;
                $data['classroom_id'] = null;
            }
        }

        $lead->fill($data);
        $changed = array_keys($lead->getDirty());
        $lead->save();

        if ($changed) {
            $labels = collect($changed)->reject(fn ($f) => in_array($f, ['child_age_months', 'updated_at'], true))
                ->map(fn ($f) => self::FIELD_LABELS[$f] ?? str_replace('_', ' ', $f))->unique()->values();
            $this->leads->log($lead, 'updated', 'Updated '.$labels->implode(', '), ['fields' => $changed], $user);
        }

        if (! $user->isSales() && $request->has('assigned_to')) {
            $to = $request->input('assigned_to') ? User::find($request->input('assigned_to')) : null;
            $this->leads->assign($lead, $to, $user);
        }

        return redirect()->route('admin.crm.leads.show', $lead)->with('success', $changed ? 'Lead updated.' : 'Nothing changed.');
    }

    public function destroy(Request $request, Lead $lead)
    {
        $reference = $lead->reference;
        $lead->delete();

        return redirect()->route('admin.crm.leads.index')->with('success', 'Lead '.$reference.' deleted.');
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $this->ensureVisible($request, $lead);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Lead::STATUSES))],
            'lost_reason' => ['nullable', 'required_if:status,lost', Rule::in(Lead::LOST_REASONS)],
            'note' => ['nullable', 'string', 'max:1000'],
        ], ['lost_reason.required_if' => 'Choose why the family is not interested.']);

        $this->leads->changeStatus($lead, $data['status'], $request->user(), $data['note'] ?? null, $data['lost_reason'] ?? null);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'status' => $lead->status,
                'label' => $lead->statusLabel(),
                'color' => $lead->statusColor(),
            ]);
        }

        return back()->with('success', 'Status changed to '.$lead->statusLabel().'.');
    }

    public function assign(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'lead_ids' => ['nullable', 'array', 'max:200'],
            'lead_ids.*' => ['integer'],
        ]);

        $to = ! empty($data['assigned_to']) ? User::find($data['assigned_to']) : null;
        $targets = ! empty($data['lead_ids']) ? Lead::whereIn('id', $data['lead_ids'])->get() : collect([$lead]);

        foreach ($targets as $target) {
            $this->leads->assign($target, $to, $request->user());
        }

        $message = $targets->count() > 1
            ? $targets->count().' leads assigned to '.($to?->name ?? 'nobody').'.'
            : ($to ? 'Assigned to '.$to->name.'.' : 'Lead unassigned.');

        return $request->expectsJson() ? response()->json(['ok' => true, 'message' => $message]) : back()->with('success', $message);
    }

    public function export(Request $request): StreamedResponse
    {
        $view = array_key_exists($request->query('view'), self::VIEWS) ? $request->query('view') : 'all';
        $query = $this->applyView($this->filteredLeads($request), $view)
            ->with(['classroom:id,name', 'branch:id,name', 'camp:id,title', 'assignee:id,name'])
            ->orderBy('id');

        $filename = 'marshmallow-leads-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Reference', 'Created at', 'Status', 'Lost reason', 'Priority', 'Parent name', 'Phone', 'WhatsApp', 'Email',
                'Child name', 'Child birthday', 'Age at school-year start', 'Academic year', 'Class', 'Branch', 'Interest', 'Camp',
                'Preferred tour', 'Heard from', 'Message', 'Assigned to', 'Next follow-up', 'Last contacted', 'Enrolled at',
                'Channel', 'Source', 'UTM source', 'UTM medium', 'UTM campaign', 'UTM content', 'UTM term', 'Landing page', 'Form page', 'Referrer',
            ]);

            $query->chunk(500, function ($leads) use ($out) {
                foreach ($leads as $lead) {
                    fputcsv($out, [
                        $lead->reference, $lead->created_at?->format('Y-m-d H:i'), $lead->statusLabel(), $lead->lost_reason,
                        Lead::PRIORITIES[$lead->priority] ?? $lead->priority, $lead->parent_name, $lead->phone, $lead->whatsapp, $lead->email,
                        $lead->child_name, $lead->child_dob?->format('Y-m-d'), $lead->childAgeLabel(), $lead->academic_year,
                        $lead->classroom?->name, $lead->branch?->name, Lead::INTERESTS[$lead->interest] ?? $lead->interest, $lead->camp?->title,
                        $lead->preferred_tour_at?->format('Y-m-d H:i'), $lead->heard_from, $lead->message, $lead->assignee?->name,
                        $lead->next_follow_up_at?->format('Y-m-d H:i'), $lead->last_contacted_at?->format('Y-m-d H:i'), $lead->enrolled_at?->format('Y-m-d H:i'),
                        Lead::CHANNELS[$lead->channel] ?? $lead->channel, $lead->source ? (Visit::SOURCES[$lead->source] ?? $lead->source) : null,
                        $lead->utm_source, $lead->utm_medium, $lead->utm_campaign, $lead->utm_content, $lead->utm_term,
                        $lead->landing_path, $lead->form_path, $lead->referrer,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function formData(Request $request, Lead $lead): array
    {
        $classrooms = Classroom::active()->get();
        $user = $request->user();

        return [
            'lead' => $lead,
            'branches' => Branch::orderBy('sort_order')->pluck('name', 'id')->all(),
            'camps' => Camp::orderByDesc('is_active')->orderBy('sort_order')->pluck('title', 'id')->all(),
            'years' => array_values(array_unique(array_filter(array_merge(ClassFinder::academicYears(), [$lead->academic_year])))),
            'channels' => $lead->exists ? Lead::CHANNELS : collect(Lead::CHANNELS)->except('website')->all(),
            'agents' => $user->isSales() ? [] : $this->assignableUsers()->pluck('name', 'id')->all(),
            'isManager' => ! $user->isSales(),
            'finder' => ClassFinder::clientConfig($classrooms),
        ];
    }

    private function duplicates(?string $phone, ?int $exceptId = null)
    {
        if (! $phone) {
            return collect();
        }

        return Lead::query()
            ->where(fn ($q) => $q->where('phone', $phone)->orWhere('whatsapp', $phone))
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->with('assignee:id,name')->latest('id')->limit(5)->get();
    }

    private function duplicateRow(Lead $lead, User $user): array
    {
        return [
            'reference' => $lead->reference,
            'parent_name' => $lead->parent_name,
            'status' => $lead->statusLabel(),
            'assignee' => $lead->assignee?->name,
            'created' => $lead->created_at?->format('j M Y'),
            'url' => ! $user->isSales() || (int) $lead->assigned_to === (int) $user->id ? route('admin.crm.leads.show', $lead) : null,
        ];
    }
}
